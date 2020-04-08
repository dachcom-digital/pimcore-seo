<?php

namespace SeoBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SeoBundle\Model\QueueEntry;

class QueueEntryRepository implements QueueEntryRepositoryInterface
{
    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(QueueEntry::class);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(array $orderBy = null)
    {
        return $this->repository->findBy([], $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllForWorker(string $workerName, array $orderBy = null)
    {
        return $this->repository->findBy(['worker' => $workerName], $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function findAtLeastOneForWorker(string $workerName)
    {
        return $this->repository->findOneBy(['worker' => $workerName]);
    }
}
