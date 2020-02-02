<?php

namespace App\Listener;

use App\Entity\Organization;
use App\Entity\OrganizationChange;
use App\Event\CitizenDeletedEvent;
use App\Event\CitizenFiredEvent;
use App\Event\CitizenFleetUpdatedEvent;
use App\Event\CitizenRefreshedEvent;
use App\Event\OrganizationPolicyChangedEvent;
use App\Repository\OrganizationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateOrganizationChangeSubscriber implements EventSubscriberInterface
{
    private OrganizationRepository $organizationRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(OrganizationRepository $organizationRepository, EntityManagerInterface $entityManager)
    {
        $this->organizationRepository = $organizationRepository;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CitizenFleetUpdatedEvent::class => 'onCitizenFleetUpdated',
            CitizenRefreshedEvent::class => 'onCitizenRefreshed',
            OrganizationPolicyChangedEvent::class => 'onOrganizationPolicyChangedEvent',
            CitizenDeletedEvent::class => 'onCitizenDeleted',
            CitizenFiredEvent::class => 'onCitizenFired',
        ];
    }

    public function onCitizenFired(CitizenFiredEvent $event): void
    {
        dump('onCitizenFired');
        $firedCitizen = $event->getFiredCitizen();

        $change = new OrganizationChange(Uuid::uuid4());
        $change->setAuthor(null);
        $change->setOrganization($event->getFiringOrga());
        $change->setType(OrganizationChange::TYPE_DELETED_CITIZEN);
        $change->setPayload([
            'handle' => $firedCitizen->getActualHandle()->getHandle(),
            'nickname' => $firedCitizen->getNickname(),
            'number' => $firedCitizen->getNumber()->getNumber(),
        ]);
        $this->entityManager->persist($change);
    }

    public function onCitizenDeleted(CitizenDeletedEvent $event): void
    {
        $deletedCitizen = $event->getDeletedCitizen();

        foreach ($deletedCitizen->getOrganizations() as $citizenOrga) {
            $change = new OrganizationChange(Uuid::uuid4());
            $change->setAuthor(null);
            $change->setOrganization($citizenOrga->getOrganization());
            $change->setType(OrganizationChange::TYPE_DELETED_CITIZEN);
            $change->setPayload([
                'handle' => $deletedCitizen->getActualHandle()->getHandle(),
                'nickname' => $deletedCitizen->getNickname(),
                'number' => $deletedCitizen->getNumber()->getNumber(),
            ]);
            $this->entityManager->persist($change);
        }
        $this->entityManager->flush();
    }

    public function onCitizenFleetUpdated(CitizenFleetUpdatedEvent $event): void
    {
        $citizen = $event->getCitizen();
        $newFleet = $event->getNewFleet();
        $oldFleet = $event->getOldFleet();

        if ($oldFleet === null) {
            $payload = [];
            foreach ($newFleet->getShips() as $ship) {
                $payloadFound = false;
                foreach ($payload as &$payloadShip) {
                    if (mb_strtolower($payloadShip['ship']) === mb_strtolower($ship->getName())) {
                        ++$payloadShip['count'];
                        $payloadFound = true;
                        break;
                    }
                }
                unset($payloadShip);
                if (!$payloadFound) {
                    $payload[] = [
                        'ship' => $ship->getName(),
                        'manu' => $ship->getManufacturer(),
                        'count' => 1,
                    ];
                }
            }
            foreach ($citizen->getOrganizations() as $citizenOrga) {
                $change = new OrganizationChange(Uuid::uuid4());
                $change->setAuthor($citizen);
                $change->setOrganization($citizenOrga->getOrganization());
                $change->setType(OrganizationChange::TYPE_UPLOAD_FLEET);
                $change->setPayload($payload);
                $this->entityManager->persist($change);
            }
            $this->entityManager->flush();

            return;
        }

        $countShips = [];
        foreach ($oldFleet->getShips() as $oldShip) {
            $oldShipName = mb_strtolower($oldShip->getName());
            if (!isset($countShips[$oldShipName])) {
                $countShips[$oldShipName] = [
                    'ship' => $oldShip->getName(),
                    'manu' => $oldShip->getManufacturer(),
                    'count' => 0,
                ];
            }
            --$countShips[$oldShipName]['count'];
        }
        foreach ($newFleet->getShips() as $newShip) {
            $newShipName = mb_strtolower($newShip->getName());
            if (!isset($countShips[$newShipName])) {
                $countShips[$newShipName] = [
                    'ship' => $newShip->getName(),
                    'manu' => $newShip->getManufacturer(),
                    'count' => 0,
                ];
            }
            ++$countShips[$newShipName]['count'];
        }

        $payloadShips = array_values(array_filter($countShips, static function (array $payloadShip) {
            return $payloadShip['count'] !== 0;
        }));
        foreach ($citizen->getOrganizations() as $citizenOrga) {
            $change = new OrganizationChange(Uuid::uuid4());
            $change->setAuthor($citizen);
            $change->setOrganization($citizenOrga->getOrganization());
            $change->setType(OrganizationChange::TYPE_UPLOAD_FLEET);
            $change->setPayload($payloadShips);
            $this->entityManager->persist($change);
        }
        $this->entityManager->flush();
    }

    public function onCitizenRefreshed(CitizenRefreshedEvent $event): void
    {
        $infos = $event->getCitizenInfos();
        $citizen = $event->getCitizenBeforeChange();

        // join orga ?
        foreach ($infos->organizations as $newOrgaInfo) {
            /** @var Organization $newOrga */
            $newOrga = $this->organizationRepository->findOneBy(['organizationSid' => $newOrgaInfo->sid->getSid()]);
            $found = false;
            foreach ($citizen->getOrganizations() as $oldOrga) {
                if ($newOrgaInfo->sid->getSid() === $oldOrga->getOrganization()->getOrganizationSid()) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                // new Orga
                $change = new OrganizationChange(Uuid::uuid4());
                $change->setAuthor($citizen);
                $change->setOrganization($newOrga);
                $change->setType(OrganizationChange::TYPE_JOIN_ORGA);
                $this->entityManager->persist($change);
            }
        }
        // leave orga ?
        foreach ($citizen->getOrganizations() as $oldOrga) {
            $found = false;
            foreach ($infos->organizations as $newOrgaInfo) {
                if ($newOrgaInfo->sid->getSid() === $oldOrga->getOrganization()->getOrganizationSid()) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                // new Orga
                $change = new OrganizationChange(Uuid::uuid4());
                $change->setAuthor($citizen);
                $change->setOrganization($oldOrga->getOrganization());
                $change->setType(OrganizationChange::TYPE_LEAVE_ORGA);
                $this->entityManager->persist($change);
            }
        }

        $this->entityManager->flush();
    }

    public function onOrganizationPolicyChangedEvent(OrganizationPolicyChangedEvent $event): void
    {
        $change = new OrganizationChange(Uuid::uuid4());
        $change->setType(OrganizationChange::TYPE_UPDATE_PRIVACY_POLICY);
        $change->setOrganization($event->getOrganization());
        $change->setAuthor($event->getAuthor());
        $change->setPayload([
            'oldValue' => $event->getOldPolicy(),
            'newValue' => $event->getNewPolicy(),
        ]);
        $this->entityManager->persist($change);
        $this->entityManager->flush();
    }
}
