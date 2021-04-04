<?php

namespace App\Controller\Funding;

use App\Entity\Funding;
use App\Event\FundingUpdatedEvent;
use App\Message\Funding\SendOrderRefundMail;
use App\Repository\FundingRepository;
use App\Service\Funding\PaypalCheckout;
use App\Service\Funding\PaypalCheckoutInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class PaypalWebhookController extends AbstractController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private PaypalCheckoutInterface $paypalCheckout,
        private FundingRepository $fundingRepository,
        private DecoderInterface $decoder,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    #[Route("/api/funding/paypal-webhook", name: "funding_paypal_webhook", methods: ["POST"])]
    public function __invoke(
        Request $request
    ): Response {
        if (!$this->paypalCheckout->verifySignature($request)) {
            return new JsonResponse(['error' => 'bad signature.'], 400);
        }

        $payload = $this->decoder->decode($request->getContent(), $request->getContentType());

        $this->logger->info('[PayPal Webhook] new event {event} fired.', ['event' => $payload['event_type'], 'payload' => $payload]);

        switch ($payload['event_type']) {
            case 'PAYMENT.CAPTURE.REVERSED':
            case 'PAYMENT.CAPTURE.REFUNDED':
                $this->handlePaymentCaptureRefunded($payload);
                break;
            case 'PAYMENT.CAPTURE.DENIED':
                $this->handlePaymentCaptureDenied($payload);
                break;
            case 'PAYMENT.CAPTURE.COMPLETED':
                $this->handlePaymentCaptureCompleted($payload);
                break;
            case 'CHECKOUT.ORDER.APPROVED':
                $this->handleCheckoutOrderApproved($payload);
                break;
            case 'PAYMENT.SALE.COMPLETED':
                $this->handlePaymentSaleCompleted($payload);
                break;
            default:
                $this->logger->warning('[PayPal Webhook] the event {event} is not implemented.', ['event' => $payload['event_type'], 'payload' => $payload]);

                return new JsonResponse(sprintf('The event %s is not implemented yet.', $payload['event_type']), 200);
        }

        return new JsonResponse(null, 204);
    }

    private function handlePaymentCaptureCompleted(array $payload): void
    {
        $this->entityManager->clear();

        $fundingId = $payload['resource']['custom_id'] ?? null;

        /** @var Funding $funding */
        $funding = $this->fundingRepository->find($fundingId);
        if ($funding === null) {
            $this->logger->error('Funding {id} not found.', ['id' => $fundingId, 'payload' => $payload]);

            throw new NotFoundHttpException(sprintf('Funding %s not found.', $fundingId));
        }

        // search if we have already handled
        if (!in_array($funding->getPaypalStatus(), ['CREATED', 'PENDING'], true)) {
            return;
        }

        $this->paypalCheckout->complete($funding);
        $this->entityManager->flush();
        $this->eventDispatcher->dispatch(new FundingUpdatedEvent($funding));
    }

    private function handleCheckoutOrderApproved(array $payload): void
    {
        $this->entityManager->clear();

        $fundingId = null;
        foreach ($payload['resource']['purchase_units'] as $purchaseUnit) {
            if ($purchaseUnit['reference_id'] === PaypalCheckout::BACKING_REFID) {
                $fundingId = $purchaseUnit['custom_id'] ?? null;
                break;
            }
        }

        /** @var Funding $funding */
        $funding = $this->fundingRepository->find($fundingId);
        if ($funding === null) {
            $this->logger->error('Funding {id} not found.', ['id' => $fundingId, 'payload' => $payload]);

            throw new NotFoundHttpException(sprintf('Funding %s not found.', $fundingId));
        }

        // search if we have already handled
        if (!in_array($funding->getPaypalStatus(), ['CREATED', 'PENDING'], true)) {
            return;
        }

        $this->paypalCheckout->complete($funding);
        $this->entityManager->flush();
        $this->eventDispatcher->dispatch(new FundingUpdatedEvent($funding));
    }

    private function handlePaymentSaleCompleted(array $payload): void
    {
        $this->entityManager->clear();

        $fundingId = $payload['resource']['custom'] ?? null;

        /** @var Funding $funding */
        $funding = $this->fundingRepository->find($fundingId);
        if ($funding === null) {
            $this->logger->error('Funding {id} not found.', ['id' => $fundingId, 'payload' => $payload]);

            throw new NotFoundHttpException(sprintf('Funding %s not found.', $fundingId));
        }

        // search if we have already handled
        if (!in_array($funding->getPaypalStatus(), ['CREATED', 'PENDING'], true)) {
            return;
        }

        $this->paypalCheckout->complete($funding);
        $this->entityManager->flush();
        $this->eventDispatcher->dispatch(new FundingUpdatedEvent($funding));
    }

    private function handlePaymentCaptureRefunded(array $payload): void
    {
        $this->entityManager->clear();

        $fundingId = $payload['resource']['custom_id'] ?? null;

        /** @var Funding $funding */
        $funding = $this->fundingRepository->find($fundingId);
        if ($funding === null) {
            $this->logger->error('Funding {id} not found.', ['id' => $fundingId, 'payload' => $payload]);

            throw new NotFoundHttpException(sprintf('Funding %s not found.', $fundingId));
        }

        // search if we have already handled this Refund by checking its ID
        $purchase = $funding->getPaypalPurchase();
        if (isset($purchase['payments']['refunds'])) {
            foreach ($purchase['payments']['refunds'] as $purchaseRefund) {
                if ($purchaseRefund['id'] === $payload['resource']['id']) {
                    // already handled
                    return;
                }
            }
        }

        $this->paypalCheckout->refund($funding);
        $this->entityManager->flush();
        $this->eventDispatcher->dispatch(new FundingUpdatedEvent($funding));

        $this->bus->dispatch(new SendOrderRefundMail($funding->getId()));
    }

    private function handlePaymentCaptureDenied(array $payload): void
    {
        $this->entityManager->clear();

        $fundingId = $payload['resource']['custom_id'] ?? null;

        /** @var Funding $funding */
        $funding = $this->fundingRepository->find($fundingId);
        if ($funding === null) {
            $this->logger->error('Funding {id} not found.', ['id' => $fundingId, 'payload' => $payload]);

            throw new NotFoundHttpException(sprintf('Funding %s not found.', $fundingId));
        }

        if ($funding->getPaypalStatus() === 'DENIED') {
            return;
        }

        $this->paypalCheckout->deny($funding);
        $this->entityManager->flush();
        $this->eventDispatcher->dispatch(new FundingUpdatedEvent($funding));
    }
}
