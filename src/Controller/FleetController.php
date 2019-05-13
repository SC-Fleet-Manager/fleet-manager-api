<?php

namespace App\Controller;

use App\Domain\ShipInfo;
use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Repository\UserRepository;
use App\Service\ShipInfosProviderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class FleetController extends AbstractController
{
    private $security;
    private $citizenRepository;
    private $userRepository;
    private $shipInfosProvider;

    public function __construct(
        Security $security,
        CitizenRepository $citizenRepository,
        UserRepository $userRepository,
        ShipInfosProviderInterface $shipInfosProvider
    ) {
        $this->security = $security;
        $this->citizenRepository = $citizenRepository;
        $this->userRepository = $userRepository;
        $this->shipInfosProvider = $shipInfosProvider;
    }

    /**
     * @Route("/my-fleet", name="my_fleet", methods={"GET"}, options={"expose":true})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED"))
     */
    public function myFleet(): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return $this->json([
                'error' => 'no_citizen_created',
                'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/#/profile">profile page</a>.',
            ], 400);
        }
        $fleet = $citizen->getLastVersionFleet();
        $shipInfos = $this->shipInfosProvider->getAllShips();

        return $this->json([
            'fleet' => $fleet,
            'shipInfos' => $shipInfos,
        ], 200, [], ['groups' => ['my-fleet']]);
    }

    /**
     * @Route("/user-fleet/{handle}", name="user_fleet", methods={"GET"}, options={"expose":true})
     */
    public function userFleet(string $handle): Response
    {
        /** @var Citizen|null $citizen */
        $citizen = $this->citizenRepository->findOneBy(['actualHandle' => $handle]);
        if ($citizen === null) {
            throw $this->createNotFoundException(sprintf('Citizen %s does not exist.', $handle));
        }

        /** @var User|null $me */
        $me = $this->getUser();

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['citizen' => $citizen]);
        if ($user === null) {
            throw $this->createNotFoundException(sprintf('User of citizen %s does not exist.', $handle));
        }

        // TODO : make a voter
        if ($user->getPublicChoice() === User::PUBLIC_CHOICE_PRIVATE
            && (!$this->security->isGranted('IS_AUTHENTICATED_REMEMBERED') || !$me->getId()->equals($user->getId()))) {
            return $this->json([
                'error' => 'no_rights',
                'errorMessage' => 'You have no rights to see this fleet.',
            ], 400);
        }
        if ($user->getPublicChoice() === User::PUBLIC_CHOICE_ORGANIZATION
            && ($me->getCitizen() === null || empty(array_intersect($citizen->getOrganisations(), $me->getCitizen()->getOrganisations())))) {
            return $this->json([
                'error' => 'no_rights',
                'errorMessage' => 'You have no rights to see this fleet.',
            ], 400);
        }

        $fleet = $citizen->getLastVersionFleet();
        $shipInfos = $this->shipInfosProvider->getAllShips();

        return $this->json([
            'fleet' => $fleet,
            'shipInfos' => $shipInfos,
        ], 200, [], ['groups' => ['my-fleet']]);
    }

    /**
     * @Route("/fleets/{organisation}", name="fleets", methods={"GET"}, options={"expose":true})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED"))
     */
    public function fleets(Request $request, string $organisation): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return $this->json([
                'error' => 'no_citizen_created',
                'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/#/profile">profile page</a>.',
            ], 400);
        } elseif (!$citizen->hasOrganisation($organisation)) {
            return $this->json([
                'error' => 'bad_organisation',
                'errorMessage' => sprintf('The organisation %s does not exist.', $organisation),
            ], 400);
        }

        $citizenIdsFilter = $request->query->get('citizens', []);
        $shipNamesFilter = $request->query->get('ships', []);

        $citizens = $this->citizenRepository->getByOrganisation(new SpectrumIdentification($organisation));
        $citizensFiltered = $this->filterCitizenByIds($citizens, $citizenIdsFilter);

        $shipInfos = $this->shipInfosProvider->getAllShips();
        $shipInfosFiltered = $this->filterShipsByNames($shipInfos, $shipNamesFilter);

        $shipCounter = $this->countHowManyCitizenHaveSameShip($shipInfosFiltered, $citizensFiltered);
        $shipInfosFiltered = $this->filterShipsGotByAtLeastOneCitizen($shipInfosFiltered, $shipCounter);

        $tableHeaders = [
            'shipName' => [
                'label' => 'Ships',
                'sortable' => true,
            ],
            'shipManufacturer' => [
                'label' => 'Manufacturers',
                'sortable' => true,
            ],
            'totalAvailable' => [
                'label' => 'Total available',
                'sortable' => true,
            ],
        ];
        foreach ($citizensFiltered as $citizen) {
            $tableHeaders[(string) $citizen->getActualHandle()] = [
                'label' => (string) $citizen->getActualHandle(),
                'sortable' => true,
            ];
        }

        $viewFleets = [];
        foreach ($shipInfosFiltered as $shipInfo) {
            $viewFleet = [
                '_cellVariants' => ['shipName' => $shipInfo->productionStatus === ShipInfo::FLIGHT_READY ? 'success' : 'danger'],
                'shipName' => $shipInfo->name,
                'shipManufacturer' => $shipInfo->manufacturerCode,
                'totalAvailable' => \array_reduce($shipCounter[$shipInfo->name], function (int $carry, int $countPerUser): int {
                    return $carry + $countPerUser;
                }, 0),
            ];
            foreach ($citizensFiltered as $citizen) {
                $count = $shipCounter[$shipInfo->name][$citizen->getId()->toString()] ?? null;
                $viewFleet[(string) $citizen->getActualHandle()] = $count;
                $viewFleet['_cellVariants'][(string) $citizen->getActualHandle()] = $count ? 'success' : '';
            }
            $viewFleets[] = $viewFleet;
        }

        $viewShips = [];
        foreach ($shipInfos as $shipInfo) {
            $viewShips[$shipInfo->name] = $shipInfo->name;
        }

        $viewCitizens = [];
        foreach ($citizens as $citizen) {
            $viewCitizens[$citizen->getId()->toString()] = (string) $citizen->getActualHandle();
        }

        return $this->json([
            'tableHeaders' => $tableHeaders,
            'fleets' => $viewFleets,
            'ships' => $viewShips,
            'citizens' => $viewCitizens,
            'shipInfos' => $shipInfosFiltered,
        ]);
    }

    /**
     * @param array|Citizen[]   $citizensFiltered
     * @param iterable|string[] $citizenIdsFilter
     *
     * @return array|Citizen[]
     */
    private function filterCitizenByIds(array $citizens, iterable $citizenIdsFilter): array
    {
        if (empty($citizenIdsFilter)) {
            return $citizens;
        }
        $citizensFiltered = \array_filter($citizens, function (Citizen $citizen) use ($citizenIdsFilter): bool {
            foreach ($citizenIdsFilter as $citizenIdFilter) {
                if ($citizen->getId()->toString() === $citizenIdFilter) {
                    return true;
                }
            }

            return false;
        });

        return $citizensFiltered;
    }

    /**
     * @param array|ShipInfo[]  $ships
     * @param iterable|string[] $shipNamesFilter
     *
     * @return array|ShipInfo[]
     */
    private function filterShipsByNames(array $ships, iterable $shipNamesFilter): array
    {
        if (empty($shipNamesFilter)) {
            return $ships;
        }
        $shipInfosFiltered = \array_filter($ships, function (ShipInfo $shipInfo) use ($shipNamesFilter): bool {
            foreach ($shipNamesFilter as $shipNameFilter) {
                if ($shipInfo->name === $shipNameFilter) {
                    return true;
                }
            }

            return false;
        });

        return $shipInfosFiltered;
    }

    /**
     * @param array|ShipInfo[] $shipInfos
     * @param array|Citizen[]  $citizens
     *
     * @return array e.g., [<shipName> => [<citizenId_1> => 2, <citizenId_2> => 1]]
     */
    private function countHowManyCitizenHaveSameShip(array $shipInfos, array $citizens): array
    {
        $shipCounter = [];
        foreach ($shipInfos as $shipInfo) {
            $shipCounter[$shipInfo->name] = [];
            foreach ($citizens as $citizen) {
                $fleet = $citizen->getLastVersionFleet();
                if ($fleet === null) {
                    continue;
                }
                foreach ($fleet->getShips() as $ship) {
                    if ($ship->getName() === $shipInfo->name) {
                        $shipCounter[$shipInfo->name][$citizen->getId()->toString()] = ($shipCounter[$shipInfo->name][$citizen->getId()->toString()] ?? 0) + 1;
                    }
                }
            }
        }

        return $shipCounter;
    }

    /**
     * @param array|ShipInfo[] $shipInfos
     *
     * @return array|ShipInfo[]
     */
    private function filterShipsGotByAtLeastOneCitizen(array $shipInfos, array &$shipCounter): array
    {
        return \array_filter($shipInfos, function (ShipInfo $shipInfo) use (&$shipCounter): bool {
            return \count($shipCounter[$shipInfo->name]) > 0;
        });
    }
}
