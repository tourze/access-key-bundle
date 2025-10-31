<?php

namespace Tourze\AccessKeyBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\AccessKeyBundle\Entity\AccessKey;
use Tourze\AccessKeyBundle\Entity\AccessKeyStatistics;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<AccessKeyStatistics>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: AccessKeyStatistics::class)]
class AccessKeyStatisticsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccessKeyStatistics::class);
    }

    public function remove(AccessKeyStatistics $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOrCreateForHour(AccessKey $accessKey, \DateTimeImmutable $hour): AccessKeyStatistics
    {
        $hourTruncated = $hour->setTime((int) $hour->format('H'), 0, 0);

        $statistics = $this->findOneBy([
            'accessKey' => $accessKey,
            'hour' => $hourTruncated,
        ]);

        if (null === $statistics) {
            $statistics = new AccessKeyStatistics();
            $statistics->setAccessKey($accessKey);
            $statistics->setHour($hourTruncated);
            $this->save($statistics);
        }

        return $statistics;
    }

    public function save(AccessKeyStatistics $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return AccessKeyStatistics[]
     */
    public function findStatisticsByAccessKeyAndDateRange(
        AccessKey $accessKey,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
    ): array {
        /** @var AccessKeyStatistics[] $result */
        $result = $this->createQueryBuilder('s')
            ->where('s.accessKey = :accessKey')
            ->andWhere('s.hour >= :startDate')
            ->andWhere('s.hour < :endDate')
            ->setParameter('accessKey', $accessKey)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('s.hour', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSummaryByAccessKey(AccessKey $accessKey, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        /** @var array{totalSuccess: numeric-string|null, totalFailure: numeric-string|null} $result */
        $result = $this->createQueryBuilder('s')
            ->select('SUM(s.successCount) as totalSuccess, SUM(s.failureCount) as totalFailure')
            ->where('s.accessKey = :accessKey')
            ->andWhere('s.hour >= :startDate')
            ->andWhere('s.hour < :endDate')
            ->setParameter('accessKey', $accessKey)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleResult()
        ;

        $totalSuccess = (int) ($result['totalSuccess'] ?? 0);
        $totalFailure = (int) ($result['totalFailure'] ?? 0);
        $totalCalls = $totalSuccess + $totalFailure;

        return [
            'totalSuccess' => $totalSuccess,
            'totalFailure' => $totalFailure,
            'totalCalls' => $totalCalls,
            'successRate' => $totalCalls > 0 ? $totalSuccess / $totalCalls : 0.0,
        ];
    }
}
