<?php

namespace App\Service;

use App\Domain\CitizenInfos;
use App\Domain\CitizenNumber;
use App\Domain\CitizenOrganizationInfo;
use App\Domain\HandleSC;
use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Exception\NotFoundHandleSCException;

class FakeCitizenInfosProvider implements CitizenInfosProviderInterface
{
    private const BLACKLIST_HANDLES = ['not_found', 'not_exist'];

    /** @var Citizen */
    private $citizen;

    /**
     * Simulates a provider DB.
     *
     * @var CitizenInfos[]
     */
    private $knownCitizens = [];

    public function __construct()
    {
        $this->citizen = new Citizen();
        $this->citizen->setActualHandle(new HandleSC('foobar'));
        $this->citizen->setNickname('Foo bar');
        $this->citizen->setNumber(new CitizenNumber('123456'));

        $citizenInfos = new CitizenInfos(new CitizenNumber('135790'), new HandleSC('fake_citizen_1'));
        $citizenInfos->mainOrga = new CitizenOrganizationInfo(new SpectrumIdentification('flk'), 3, 'Soldier');
        $citizenInfos->organizations[] = $citizenInfos->mainOrga;
        $citizenInfos->organizations[] = new CitizenOrganizationInfo(new SpectrumIdentification('gardiens'), 4, 'Captain');
        $citizenInfos->nickname = 'Fake Citizen 1';
        $this->knownCitizens[] = $citizenInfos;

        $citizenInfos = new CitizenInfos(new CitizenNumber('16919861'), new HandleSC('user_nocitizen_well_formed_bio'));
        $citizenInfos->bio = '18vkFOQV3iWVggC4xuvft11FXPZUiqLzMyxTjRZMECnQTD10lvXLWB1TFt9CUOaT';
        $this->knownCitizens[] = $citizenInfos;

        $citizenInfos = new CitizenInfos(new CitizenNumber('45224582'), new HandleSC('need_refresh'));
        $citizenInfos->mainOrga = new CitizenOrganizationInfo(new SpectrumIdentification('flk'), 1, 'Newbie');
        $citizenInfos->organizations[] = $citizenInfos->mainOrga;
        $citizenInfos->organizations[] = new CitizenOrganizationInfo(new SpectrumIdentification('gardiens'), 1, 'Noob');
        $citizenInfos->nickname = 'NeedRefresh';
        $this->knownCitizens[] = $citizenInfos;
    }

    public function setCitizen(?Citizen $citizen): void
    {
        $this->citizen = $citizen;
    }

    public function setKnownCitizens(array $knownCitizens): void
    {
        $this->knownCitizens = $knownCitizens;
    }

    public function retrieveInfos(HandleSC $handleSC, bool $caching = true): CitizenInfos
    {
        if (in_array($handleSC->getHandle(), self::BLACKLIST_HANDLES, true)) {
            throw new NotFoundHandleSCException('Citizen not found.');
        }
        foreach ($this->knownCitizens as $knownCitizen) {
            if ($knownCitizen->handle->getHandle() === $handleSC->getHandle()) {
                return $knownCitizen;
            }
        }
        if ($this->citizen === null) {
            throw new NotFoundHandleSCException('Citizen not found.');
        }
        $sourceMainOrga = $this->citizen->getMainOrga();
        $mainOrga = null;
        if ($sourceMainOrga !== null) {
            $mainOrga = new CitizenOrganizationInfo(
                new SpectrumIdentification($sourceMainOrga->getOrganizationSid()),
                $sourceMainOrga->getRank(),
                $sourceMainOrga->getRankName()
            );
        }

        $orgas = [];
        foreach ($this->citizen->getOrganizations() as $citizenOrga) {
            $orga = new CitizenOrganizationInfo(
                new SpectrumIdentification($citizenOrga->getOrganizationSid()),
                $citizenOrga->getRank(),
                $citizenOrga->getRankName()
            );
            $orgas[] = $orga;
        }

        $ci = new CitizenInfos(
            clone $this->citizen->getNumber(),
            clone $this->citizen->getActualHandle()
        );
        $ci->nickname = $this->citizen->getNickname();
        $ci->countRedactedOrganizations = $this->citizen->getCountRedactedOrganizations();
        $ci->redactedMainOrga = $this->citizen->isRedactedMainOrga();
        $ci->mainOrga = $mainOrga;
        $ci->organizations = [];
        if ($mainOrga !== null) {
            $ci->organizations[] = $mainOrga;
        }
        $ci->organizations = array_merge($ci->organizations, $orgas);
        $ci->bio = $this->citizen->getBio();
        $ci->avatarUrl = 'http://example.com/fake-avatar.png';
        $ci->registered = new \DateTimeImmutable('2018-01-01 12:00:00');

        return $ci;
    }
}
