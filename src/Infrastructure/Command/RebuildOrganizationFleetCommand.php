<?php

namespace App\Infrastructure\Command;

use App\Application\Common\Clock;
use App\Application\Provider\MemberProfileProviderInterface;
use App\Domain\OrganizationShipId;
use App\Domain\Service\EntityIdGeneratorInterface;
use App\Domain\UserId;
use App\Entity\Organization;
use App\Entity\OrganizationFleet;
use App\Infrastructure\Repository\Fleet\DoctrineFleetRepository;
use App\Infrastructure\Repository\Organization\DoctrineOrganizationFleetRepository;
use App\Infrastructure\Repository\Organization\DoctrineOrganizationRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RebuildOrganizationFleetCommand extends Command
{
    protected static $defaultName = 'app:organizations:rebuild-fleets';

    public function __construct(
        private DoctrineFleetRepository $fleetRepository,
        private DoctrineOrganizationRepository $organizationRepository,
        private DoctrineOrganizationFleetRepository $organizationFleetRepository,
        private MemberProfileProviderInterface $memberProfileProvider,
        private EntityIdGeneratorInterface $entityIdGenerator,
        private Clock $clock,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Organization[] $orgas */
        $orgas = $this->organizationRepository->findAll();
        foreach ($orgas as $orga) {
            $orga = $this->organizationRepository->find((string) $orga->getId());
            if ($orga === null) {
                continue;
            }
            $output->writeln('<info>Organization '.$orga->getName().'...</info>');
            $orgaFleet = $this->organizationFleetRepository->getOrganizationFleet($orga->getId());
            if ($orgaFleet === null) {
                $orgaFleet = new OrganizationFleet($orga->getId(), $this->clock->now());
            }
            $orgaFleet->clearShips($this->clock->now());

            $members = $orga->getJoinedMembers($this->memberProfileProvider);
            $output->writeln(count($members).' members...');
            foreach ($members as $member) {
                $output->writeln('Member '.$member->getHandle().'...');
                $fleet = $this->fleetRepository->getFleetByUser(UserId::fromString((string) $member->getId()));
                if ($fleet === null) {
                    continue;
                }
                foreach ($fleet->getShips() as $ship) {
                    $output->writeln('Ship '.$ship->getModel().'...');
                    $orgaFleet->createOrUpdateShip(
                        $member->getId(),
                        $ship->getModel(),
                        $ship->getImageUrl(),
                        $ship->getQuantity(),
                        $this->clock->now(),
                        $this->entityIdGenerator,
                    );
                }
            }
            unset($members);
            $this->organizationFleetRepository->saveAll([$orgaFleet]);
        }

        return 0;
    }
}
