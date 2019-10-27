<?php

namespace App\Controller\Profile;

use App\Domain\HandleSC;
use App\Exception\NotFoundHandleSCException;
use App\Form\Dto\LinkAccount;
use App\Form\LinkAccountForm;
use App\Service\Citizen\InfosProvider\CitizenInfosProviderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class LinkAccountSearchHandleController
{
    private $formFactory;
    private $citizenInfosProvider;
    private $serializer;

    public function __construct(FormFactoryInterface $formFactory, CitizenInfosProviderInterface $citizenInfosProvider, SerializerInterface $serializer)
    {
        $this->formFactory = $formFactory;
        $this->citizenInfosProvider = $citizenInfosProvider;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/api/profile/search-handle", name="search_handle", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $handle = $request->query->get('handle');

        $linkAccount = new LinkAccount();
        $form = $this->formFactory->createNamedBuilder('', LinkAccountForm::class, $linkAccount)->getForm();
        $form->submit(['handleSC' => $handle]);

        if (!$form->isValid()) {
            $formErrors = $form->getErrors(true);
            $errors = [];
            foreach ($formErrors as $formError) {
                $errors[] = $formError->getMessage();
            }

            return new JsonResponse($this->serializer->serialize([
                'error' => 'invalid_form',
                'formErrors' => $errors,
            ], 'json'), 400, [], true);
        }

        try {
            $citizenInfos = $this->citizenInfosProvider->retrieveInfos(new HandleSC($linkAccount->handleSC));

            return new JsonResponse($this->serializer->serialize($citizenInfos, 'json'), 200, [], true);
        } catch (NotFoundHandleSCException $e) {
        }

        return new JsonResponse($this->serializer->serialize([
            'error' => 'not_found_handle',
            'errorMessage' => sprintf('The SC handle %s does not exist.', $handle),
        ], 'json'), 404, [], true);
    }
}
