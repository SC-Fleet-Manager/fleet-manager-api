<?php

namespace App\MessageHandler\Funding;

use App\Message\Funding\SendOrderCaptureSummaryMail;
use App\Repository\FundingRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Address;

class SendOrderCaptureSummaryMailHandler implements MessageHandlerInterface
{
    public function __construct(
        private FundingRepository $fundingRepository,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private string $noreplyAddress,
        private array $orderCaptureSummaryAddresses
    ) {
    }

    public function __invoke(SendOrderCaptureSummaryMail $message): void
    {
        $funding = $this->fundingRepository->find($message->getFundingId()->getId());
        if ($funding === null) {
            throw new \LogicException(sprintf('Funding %s does not exist.', $message->getFundingId()));
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->noreplyAddress, 'Fleet Manager'))
            ->to(...$this->orderCaptureSummaryAddresses)
            ->subject('[ADMIN] Fleet Manager : Order Capture')
            ->textTemplate('emails/order_capture_summary.txt.twig')
            ->context(['funding' => $funding]);
        try {
            $this->mailer->send($email);
        } catch (\Throwable $exception) {
            $this->logger->error('Unable to send OrderCaptureSummary mail', ['exception' => $exception]);
        }
    }
}
