<?php

namespace App\Controller\Funding;

use App\Entity\Funding;
use App\Event\FundingRefundedEvent;
use App\Form\Dto\FundingRefund;
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
        dump($payload);

        $request->headers->get('paypal-auth-algo');
        $request->headers->get('paypal-auth-version');
        $request->headers->get('paypal-cert-url');
        $request->headers->get('paypal-transmission-id');
        $request->headers->get('paypal-transmission-sig');
        $request->headers->get('paypal-transmission-time');

        /*
        request headers:

        paypal-auth-algo "SHA256withRSA"

        paypal-auth-version "v2"

        paypal-cert-url "https://api.paypal.com/v1/notifications/certs/CERT-360caa42-fca2a594-5edc0ebc"

        paypal-transmission-id "95e4c6b0-1850-11ea-b6db-1dcbc04d43b2"

        paypal-transmission-sig "NYlwg1FPNSYduVzKpFY//ImrIALdCQ93Dr4KwlphtW/+l75v9OVJALCFWEtoEoQNFZKoQA15QRlKODRLch5bl6RXCn+X8goXdQJU0y6W75Y+sKsLTZ1jdIcrj4N9ZdwfGJPrZyeGuErdgxATqvgrbcrnOSmfSYrR8jwoK1L3gFdcVoWHKzlvnKUs6YiqCBRQKwjPlFbKseoSP/KvNfN5wuZtTStZIis+etZSFRbkLi9RWN3nn3IttutAtyc5z222C123wIx8qvvX6xmoGnKocPbuyryxzUaSz/qhU4ISUsVPiYhFi5tsBnHDA3GJd5AH3ru3tkfOaKX4rT/xO5UrIA=="

        paypal-transmission-time "2019-12-06T17:48:17Z"
        */

        if ($payload['event_type'] === 'PAYMENT.CAPTURE.REFUNDED') {
            $this->entityManager->clear();

            /** @var Funding $funding */
            $funding = $this->fundingRepository->find($payload['resource']['custom_id']);
            if ($funding === null) {
                $this->logger->error('Funding {id} not found.', ['id' => $payload['resource']['custom_id'], 'payload' => $payload]);

                throw new NotFoundHttpException(sprintf('Funding %s not found.', $payload['resource']['custom_id']));
            }
            $refund = new FundingRefund(
                new \DateTimeImmutable($payload['create_time']),
                (int) bcmul($payload['resource']['amount']['value'], 100),
                $payload['resource']['amount']['currency_code'],
            );
            $this->paypalCheckout->refund($funding, $refund);

            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(new FundingRefundedEvent($funding));
            $this->bus->dispatch(new SendOrderRefundMail($funding->getId()));
        }

        return new JsonResponse(null, 204);
    }
}
