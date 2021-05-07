<?php

namespace App\Infrastructure\Controller\ShipTemplate;

use App\Application\ShipTemplate\CreateTemplateService;
use App\Application\ShipTemplate\Input\CreateTemplateInput;
use App\Domain\ShipTemplateId;
use App\Domain\TemplateAuthorId;
use App\Entity\CargoCapacity;
use App\Entity\Crew;
use App\Entity\Manufacturer;
use App\Entity\Price;
use App\Entity\ShipChassis;
use App\Entity\ShipRole;
use App\Entity\User;
use App\Infrastructure\Controller\ShipTemplate\Input\CreateTemplateInput as HttpCreateTemplateInput;
use Money\Money;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateTemplateController
{
    public function __construct(
        private CreateTemplateService $createTemplateService,
        private Security $security,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @OpenApi\Tag(name="ShipTemplate")
     * @OpenApi\RequestBody(
     *     @Model(type=HttpCreateTemplateInput::class)
     * )
     * @OpenApi\Response(response=204, description="Creates a new template for the logged user.")
     * @OpenApi\Response(response=400, description="Invalid payload.")
     */
    #[Route('/api/ship-template/create', name: 'ship_template_create', methods: ['POST'])]
    public function __invoke(
        Request $request
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var HttpCreateTemplateInput $input */
        $input = $this->serializer->deserialize($request->getContent(), HttpCreateTemplateInput::class, $request->getContentType());
        $this->validator->validate($input);

        /** @var User $user */
        $user = $this->security->getUser();

        $createTemplate = new CreateTemplateInput(
            $input->model,
            $input->pictureUrl,
            new ShipChassis(
                $input->chassis->name,
            ),
            new Manufacturer(
                $input->manufacturer->name,
                $input->manufacturer->code,
            ),
            $input->createShipSize(),
            new ShipRole($input->role),
            new CargoCapacity($input->cargoCapacity ?? 0),
            new Crew($input->crew->min, $input->crew->max),
            new Price(
                $input->price->pledge !== null ? Money::USD($input->price->pledge) : null,
                $input->price->inGame !== null ? Money::UEC($input->price->inGame) : null,
            ),
        );

        $this->createTemplateService->handle(TemplateAuthorId::fromString((string) $user->getId()), new ShipTemplateId(new Ulid()), $createTemplate);

        return new Response(null, 204);
    }
}
