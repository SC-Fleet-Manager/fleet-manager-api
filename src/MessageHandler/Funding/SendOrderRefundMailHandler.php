<?php

namespace App\MessageHandler\Funding;

use App\Entity\Funding;
use App\Message\Funding\SendOrderRefundMail;
use App\Repository\FundingRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SendOrderRefundMailHandler implements MessageHandlerInterface
{
    public function __construct(
        private FundingRepository $fundingRepository,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private string $noreplyAddress
    ) {
    }

    public function __invoke(SendOrderRefundMail $message): void
    {
        /** @var Funding $funding */
//        $funding = $this->fundingRepository->find($message->getFundingId());
//        if ($funding === null) {
//            throw new \LogicException(sprintf('Funding %s does not exist.', $message->getFundingId()));
//        }

//        $email = (new TemplatedEmail())
//            ->from(new Address($this->noreplyAddress, 'Fleet Manager'))
//            ->to($funding->getUser()->getem)
//            ->subject('[ADMIN] Fleet Manager : Order Capture')
//            ->textTemplate('emails/order_capture_summary.txt.twig')
//            ->context(['funding' => $funding]);
//        try {
//            $this->mailer->send($email);
//        } catch (\Throwable $exception) {
//            $this->logger->error('Unable to send OrderCaptureSummary mail', ['exception' => $exception]);
//        }
    }
}
