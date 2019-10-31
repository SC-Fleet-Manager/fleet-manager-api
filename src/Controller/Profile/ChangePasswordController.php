<?php

namespace App\Controller\Profile;

use App\Entity\User;
use App\Form\Dto\ChangePassword;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChangePasswordController extends AbstractController
{
    private $security;
    private $entityManager;
    private $passwordEncoder;
    private $serializer;
    private $validator;

    public function __construct(
        Security $security,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * @Route("/api/profile/change-password", name="profile_change_password", methods={"POST"})
     */
    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var User $user */
        $user = $this->security->getUser();
        if (!$user->isEmailConfirmed()) {
            return $this->json([
                'error' => 'user_not_confirmed',
                'errorMessage' => 'You can\'t do that now. First, you have to confirm your email address. Please check your inbox/spams.',
            ], 400);
        }

        /** @var ChangePassword $changePassword */
        $changePassword = $this->serializer->denormalize($request->request->all(), ChangePassword::class);
        $errors = $this->validator->validate($changePassword);

        // TODO : extract in a Constraint
        if (!$this->passwordEncoder->isPasswordValid($user, $changePassword->oldPassword)) {
            $errors->add(new ConstraintViolation('Your current password is wrong.', null, [], null, 'oldPassword', null));
        }

        if ($errors->count() > 0) {
            return $this->json([
                'error' => 'invalid_form',
                'formErrors' => $errors,
            ], 400);
        }

        $user->setPassword($this->passwordEncoder->encodePassword($user, $changePassword->newPassword));
        $user->setUpdatedAt(new \DateTimeImmutable());
        unset($changePassword);
        $this->entityManager->flush();

        return $this->json(null, 204);
    }
}
