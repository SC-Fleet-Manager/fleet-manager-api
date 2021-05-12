<?php

namespace App\Infrastructure\Repository\ShipTemplate;

use App\Application\Repository\ShipTemplateRepositoryInterface;
use App\Domain\Exception\ConflictVersionException;
use App\Domain\ShipTemplateId;
use App\Entity\ShipTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineShipTemplateRepository extends ServiceEntityRepository implements ShipTemplateRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShipTemplate::class);
    }

    public function getTemplateById(ShipTemplateId $templateId): ?ShipTemplate
    {
        return $this->findOneBy(['id' => (string) $templateId]);
    }

    public function save(ShipTemplate $template): void
    {
        $this->_em->persist($template);
        try {
            $this->_em->flush();
            $this->_em->clear();
        } catch (OptimisticLockException $e) {
            $this->logger->warning('conflict version on save ship template.', ['exception' => $e]);
            throw new ConflictVersionException($template, 'Unable to save your ship template. Please, try again.', context: ['shipTemplateId' => $template->getId()], previous: $e);
        }
    }
}
