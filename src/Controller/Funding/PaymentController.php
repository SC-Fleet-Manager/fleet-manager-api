<?php

namespace App\Controller\Funding;

use App\Entity\Funding;
use App\Entity\User;
use App\Exception\UnableToCreatePaypalOrderException;
use App\Form\Dto\FundingPayment;
use App\Service\Funding\PaypalCheckoutInterface;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PaymentController extends AbstractController
{
    public function __construct(
        private Security $security,
        private PaypalCheckoutInterface $paypalCheckout,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route("/api/funding/payment", name: "funding_payment", methods: ["POST"])]
    public function __invoke(
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var FundingPayment $fundingPayment */
        $fundingPayment = $this->serializer->deserialize($request->getContent(), FundingPayment::class, $request->getContentType());
        $errors = $this->validator->validate($fundingPayment);

        if ($errors->count() > 0) {
            return $this->json([
                'error' => 'invalid_form',
                'formErrors' => $errors,
            ], 400);
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $funding = new Funding(Uuid::uuid4());
        $funding->setGateway(Funding::PAYPAL);
        $funding->setCurrency('USD');
        $funding->setAmount($fundingPayment->amount);
        $funding->setUser($user);

        try {
            $this->paypalCheckout->create($funding, $user, $request->getLocale());
        } catch (UnableToCreatePaypalOrderException $e) {
            return $this->json([
                'error' => 'paypal_error',
                'paypalError' => $e->paypalError,
            ], 400);
        }

        $this->entityManager->persist($funding);
        $this->entityManager->flush();

        return $this->json($funding, 200, [], ['groups' => 'supporter']);
    }
}
