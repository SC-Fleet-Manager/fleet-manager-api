<?php

namespace App\Service\Organization;

use App\Repository\CitizenRepository;
use App\Service\Dto\RsiOrgaMemberInfos;

class OrganizationMembersUtil
{
    private $citizenRepository;

    public function __construct(CitizenRepository $citizenRepository)
    {
        $this->citizenRepository = $citizenRepository;
    }

    public function renderMembersList(array $memberInfos): array
    {
        $countVisibleCitizens = count($memberInfos['visibleCitizens']);
        // pagination
//        $memberInfos['visibleCitizens'] = array_splice($memberInfos['visibleCitizens'], ($page - 1) * $itemsPerPage, $itemsPerPage);

        $handles = array_map(static function (RsiOrgaMemberInfos $info) {
            return mb_strtolower($info->handle);
        }, $memberInfos['visibleCitizens']);
        $citizens = $this->citizenRepository->findSomeHandlesWithLastFleet($handles);

        $members = [];
        /** @var RsiOrgaMemberInfos $memberInfo */
        foreach ($memberInfos['visibleCitizens'] as $memberInfo) {
            $memberCitizen = null;
            foreach ($citizens as $citizen) {
                if (mb_strtolower($citizen->getActualHandle()->getHandle()) === mb_strtolower($memberInfo->handle)) {
                    $memberCitizen = $citizen;
                    break;
                }
            }
            if ($memberCitizen === null) {
                $members[] = [
                    'infos' => $memberInfo,
                    'status' => RsiOrgaMemberInfos::STATUS_NOT_REGISTERED,
                ];
            } elseif ($memberCitizen->getLastFleet() === null) {
                $members[] = [
                    'infos' => $memberInfo,
                    'status' => RsiOrgaMemberInfos::STATUS_REGISTERED,
                ];
            } else {
                $members[] = [
                    'lastFleetUploadDate' => $memberCitizen->getLastFleet()->getUploadDate()->format('Y-m-d'),
                    'infos' => $memberInfo,
                    'status' => RsiOrgaMemberInfos::STATUS_FLEET_UPLOADED,
                ];
            }
        }

        // sorting
        $collator = \Collator::create(\Locale::getDefault());
        usort($members, static function (array $member1, array $member2) use ($collator) {
            $rankCmp = $member2['infos']->rank - $member1['infos']->rank;
            if ($rankCmp !== 0) {
                return $rankCmp;
            }
            if ($member1['status'] !== $member2['status']) {
                if ($member1['status'] === RsiOrgaMemberInfos::STATUS_FLEET_UPLOADED) {
                    return -1;
                }
                if ($member2['status'] === RsiOrgaMemberInfos::STATUS_FLEET_UPLOADED) {
                    return 1;
                }
                if ($member1['status'] === RsiOrgaMemberInfos::STATUS_REGISTERED) {
                    return -1;
                }
                if ($member2['status'] === RsiOrgaMemberInfos::STATUS_REGISTERED) {
                    return 1;
                }
                if ($member1['status'] === RsiOrgaMemberInfos::STATUS_NOT_REGISTERED) {
                    return -1;
                }

                return 1;
            }

            return $collator->compare($member1['infos']->handle, $member2['infos']->handle);
        });

        return [
//            'page' => $page,
//            'totalPage' => ceil($countVisibleCitizens / $itemsPerPage),
            'totalItems' => $countVisibleCitizens,
            'members' => $members,
            'countHiddenMembers' => $memberInfos['countHiddenCitizens'],
        ];
    }
}
