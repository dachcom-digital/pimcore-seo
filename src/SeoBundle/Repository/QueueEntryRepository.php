<?php

namespace SeoBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SeoBundle\Model\QueueEntry;
use SeoBundle\Model\QueueEntryInterface;

class QueueEntryRepository implements QueueEntryRepositoryInterface
{
    protected EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(QueueEntry::class);
    }

    public function findAll(?array $orderBy = null): array
    {
        return $this->repository->findBy([], $orderBy);
    }

    public function findAllForWorker(string $workerName, ?array $orderBy = null): array
    {
        return $this->repository->findBy(['worker' => $workerName], $orderBy);
    }

    public function findAtLeastOneForWorker(string $workerName): ?QueueEntryInterface
    {
        return $this->repository->findOneBy(['worker' => $workerName]);
    }
}
