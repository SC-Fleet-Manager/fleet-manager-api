<?php

namespace App\Controller\Funding;

use App\Entity\Funding;
use App\Event\FundingUpdatedEvent;
use App\Message\Funding\SendOrderRefundMail;
use App\Repository\FundingRepository;
use App\Service\Funding\PaypalCheckout;
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

    private PaypalCheckout $paypalCheckout;
    private FundingRepository $fundingRepository;
    private DecoderInterface $decoder;
    private EntityManagerInterface $entityManager;
    private MessageBusInterface $bus;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        PaypalCheckout $paypalCheckout,
        FundingRepository $fundingRepository,
        DecoderInterface $decoder,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->paypalCheckout = $paypalCheckout;
        $this->fundingRepository = $fundingRepository;
        $this->decoder = $decoder;
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/api/funding/paypal-webhook", name="funding_paypal_webhook", methods={"POST"})
     */
    public function __invoke(Request $request): Response
    {
        $payload = $this->decoder->decode($request->getContent(), $request->getContentType());
        if ($payload['event_type'] !== 'PAYMENT.CAPTURE.REFUNDED') {
            return new JsonResponse(null, 204);
        }

        if (!$this->paypalCheckout->verifySignature($request)) {
            return new JsonResponse(['error' => 'bad signature.'], 400);
        }

        switch ($payload['event_type']) {
            case 'PAYMENT.CAPTURE.REFUNDED':
                $this->handlePaymentCaptureRefunded($payload);
                break;
            default:
                $this->logger->warning('[PayPal Webhook] the event {event} is not implemented.', ['event' => $payload['event_type'], 'payload' => $payload]);

                return new JsonResponse(sprintf('The event %s is not implemented yet.', $payload['event_type']), 501);
        }

        return new JsonResponse(null, 204);
    }

    private function handlePaymentCaptureRefunded(array $payload): void
    {
        $this->entityManager->clear();

        /** @var Funding $funding */
        $funding = $this->fundingRepository->find($payload['resource']['custom_id']);
        if ($funding === null) {
            $this->logger->error('Funding {id} not found.', ['id' => $payload['resource']['custom_id'], 'payload' => $payload]);

            throw new NotFoundHttpException(sprintf('Funding %s not found.', $payload['resource']['custom_id']));
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
}
