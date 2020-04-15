<?php

namespace App\Command;

use App\Domain\CitizenNumber;
use App\Domain\HandleSC;
use App\Entity\Citizen;
use App\Entity\CitizenOrganization;
use App\Entity\Fleet;
use App\Entity\Organization;
use App\Entity\Ship;
use App\Entity\User;
use App\Repository\OrganizationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateSuperAccountCommand extends Command
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private UserPasswordEncoderInterface $userPasswordEncoder;
    private OrganizationRepository $organizationRepository;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $userPasswordEncoder,
        OrganizationRepository $organizationRepository
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->organizationRepository = $organizationRepository;
    }

    protected function configure(): void
    {
        $this->setName('app:create-super-account');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $user = $this->userRepository->find('45912bec-8175-46d2-be97-3d1aa9963e6c');
        if ($user !== null) {
            throw new RuntimeException('The Super-Admin account already exists.');
        }

        $answer = $io->askQuestion(new ConfirmationQuestion('Would you really want to create a super-admin account?', false));
        if (!$answer) {
            return 0;
        }

        $password = $io->askHidden('Which password?');

        $fleet = new Fleet(Uuid::fromString('5cac2a5c-bbce-4c9b-bb84-a9f74aff13c6'));
        $fleet->setVersion(1);
        $dql = <<<DQL
                SELECT DISTINCT ship.name
                FROM App\Entity\Ship ship
                JOIN ship.fleet fleet
                JOIN App\Entity\Citizen citizen WITH citizen.lastFleet = fleet
            DQL;
        $query = $this->entityManager->createQuery($dql);
        $shipNames = $query->getScalarResult();
        foreach ($shipNames as $shipName) {
            $this->entityManager->clear();
            $dql = <<<DQL
                    SELECT ship
                    FROM App\Entity\Ship ship
                    JOIN ship.fleet fleet
                    JOIN App\Entity\Citizen citizen WITH citizen.lastFleet = fleet
                    WHERE ship.name = :shipName
                DQL;
            $query = $this->entityManager->createQuery($dql);
            $query->setParameter('shipName', $shipName['name']);
            $query->setMaxResults(1);
            $result = $query->getOneOrNullResult();
            /** @var Ship $result */
            if ($result === null) {
                continue;
            }
            $ship = new Ship(Uuid::uuid4());
            $ship
                ->setManufacturer($result->getManufacturer())
                ->setName($result->getName())
                ->setNormalizedName($result->getNormalizedName())
                ->setGalaxyId($result->getGalaxyId())
                ->setInsuranceType($result->getInsuranceType())
                ->setInsuranceDuration($result->getInsuranceDuration())
                ->setCost($result->getCost())
                ->setPledgeDate($result->getPledgeDate())
                ->setRawData([]);
            $fleet->addShip($ship);
        }
        $this->entityManager->clear();

        $citizen = new Citizen(Uuid::fromString('4ec67c10-2e72-47f7-a596-b8c65d14f9e0'));
        $user = new User(Uuid::fromString('45912bec-8175-46d2-be97-3d1aa9963e6c'));
        $user
            ->setNickname('SuperAdmin')
            ->setCoins(50000)
            ->setCitizen($citizen)
            ->setApiToken('311b889899a748e89575df880a6d53b424189f28b265')
            ->setToken('4da3a59f54714befb8f1e47aac4f5a3e65f957d31d51')
            ->setEmail('superadmin@fleet-manager.space')
            ->setEmailConfirmed(true)
            ->setPassword($this->userPasswordEncoder->encodePassword($user, $password))
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN'])
            ->setPublicChoice(User::PUBLIC_CHOICE_PUBLIC);

        $citizen
            ->setNickname('SuperAdmin')
            ->setActualHandle(new HandleSC('superadmin'))
            ->setCountRedactedOrganizations(0)
            ->setLastFleet($fleet)
            ->addFleet($fleet)
            ->setNumber(new CitizenNumber('00000000'));

        /** @var Organization $orga */
        $orga = $this->organizationRepository->findOneBy(['id' => '892b0cc5-536c-4e69-b7c7-73dd490f29e4']);
        $citizenOrga = (new CitizenOrganization(Uuid::fromString('129d5f7f-2bfe-4f66-87f7-c603136fabbf')))
            ->setOrganization($orga)
            ->setOrganizationSid($orga->getOrganizationSid())
            ->setRank(5)
            ->setRankName('Big boss')
            ->setVisibility(CitizenOrganization::VISIBILITY_ORGA)
            ->setCitizen($citizen);
        $citizen->setMainOrga($citizenOrga);

        /** @var Organization $orga */
        $orga = $this->organizationRepository->findOneBy(['id' => '6a870d99-7792-4f6c-9f30-2bb0776d8791']);
        (new CitizenOrganization(Uuid::fromString('f6bfcaee-b5d3-49d9-b2ec-7a31a0b803c9')))
            ->setOrganization($orga)
            ->setOrganizationSid($orga->getOrganizationSid())
            ->setRank(5)
            ->setRankName('Big boss')
            ->setVisibility(CitizenOrganization::VISIBILITY_ORGA)
            ->setCitizen($citizen);

        $this->entityManager->persist($user);
        $this->entityManager->persist($citizen);
        $this->entityManager->persist($fleet);

        $this->entityManager->flush();

        $io->success('Super-Admin account created.');

        return 0;
    }
}
