<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Message\Profile\SendChangeEmailRequestMail;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Address;

class SendChangeEmailRequestMailHandler implements MessageHandlerInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private MailerInterface $mailer,
        private string $registrationFromAddress
    ) {
    }

    public function __invoke(SendChangeEmailRequestMail $message): void
    {
        /** @var User $user */
        $user = $this->userRepository->find($message->getUserId());
        if ($user === null) {
            throw new \LogicException(sprintf('User %s does not exist.', $message->getUserId()));
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->registrationFromAddress, 'Fleet Manager'))
            ->to($user->getPendingEmail())
            ->subject('Fleet Manager : Change email')
            ->textTemplate('emails/change_email.txt.twig')
            ->htmlTemplate('emails/change_email.html.twig')
            ->context(['user' => $user]);
        $this->mailer->send($email);
    }
}
