<?php

namespace App\Tests\Controller\BackOffice\ShipTransform;

use App\Entity\Ship;
use App\Entity\User;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;

class SyncFleetShipsWithProviderControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group ship_transform
     * @group bo
     */
    public function testNotAuth(): void
    {
        $this->client->request('POST', '/bo/sync-fleet-ships-with-provider');

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group ship_transform
     * @group bo
     */
    public function testNotAdmin(): void
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Gardien1']); // ROLE_USER
        $this->logIn($user);
        $this->client->request('POST', '/bo/sync-fleet-ships-with-provider');

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group ship_transform
     * @group bo
     */
    public function testAdmin(): void
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ioni']); // ROLE_ADMIN

        // add a Ship without GalaxyId
        /** @var EntityManagerInterface $em */
        $em = $this->doctrine->getManager();
        $ship = new Ship(Uuid::fromString('88cdfa6e-eba8-4610-9328-fe5c7461eac3'));
        $ship
            ->setName('Aurora MR')
            ->setManufacturer('RSI')
            ->setFleet($user->getCitizen()->getLastFleet())
            ->setGalaxyId(null)
            ->setNormalizedName(null);
        $em->persist($ship);
        $em->flush();

        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ioni']); // ROLE_ADMIN
        $this->logIn($user);
        $this->client->request('POST', '/bo/sync-fleet-ships-with-provider');

        /** @var Ship $ship */
        $ship = $this->doctrine->getRepository(Ship::class)->find($ship->getId());
        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
        $this->assertSame('cbcb60c7-a780-4a59-b51d-0ad8021813bf', $ship->getGalaxyId()->toString());
        $this->assertSame('Aurora MR', $ship->getNormalizedName());
    }
}
