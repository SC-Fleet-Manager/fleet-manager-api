<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Message\Profile\SendLinkEmailPasswordConfirmationMail;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Address;

class SendLinkEmailPasswordConfirmationMailHandler implements MessageHandlerInterface
{
    private $userRepository;
    private $mailer;
    private $registrationFromAddress;

    public function __construct(UserRepository $userRepository, MailerInterface $mailer, string $registrationFromAddress)
    {
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
        $this->registrationFromAddress = $registrationFromAddress;
    }

    public function __invoke(SendLinkEmailPasswordConfirmationMail $message): void
    {
        /** @var User $user */
        $user = $this->userRepository->find($message->getUserId());
        if ($user === null) {
            throw new \LogicException(sprintf('User %s does not exist.', $message->getUserId()));
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->registrationFromAddress, 'Fleet Manager'))
            ->to($user->getEmail())
            ->subject('Fleet Manager : Link email/password confirmation')
            ->textTemplate('emails/link_email_password_confirmation.txt.twig')
            ->htmlTemplate('emails/link_email_password_confirmation.html.twig')
            ->context(['user' => $user]);
        $this->mailer->send($email);
    }
}
