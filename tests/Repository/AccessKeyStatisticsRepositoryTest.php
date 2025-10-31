<?php

namespace Tourze\AccessKeyBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AccessKeyBundle\Entity\AccessKey;
use Tourze\AccessKeyBundle\Entity\AccessKeyStatistics;
use Tourze\AccessKeyBundle\Repository\AccessKeyStatisticsRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(AccessKeyStatisticsRepository::class)]
#[RunTestsInSeparateProcesses]
final class AccessKeyStatisticsRepositoryTest extends AbstractRepositoryTestCase
{
    protected function getEntityClass(): string
    {
        return AccessKeyStatistics::class;
    }

    protected function createNewEntity(): object
    {
        $accessKey = new AccessKey();
        $accessKey->setTitle('Test Key ' . uniqid());
        $accessKey->setAppId('test-app-' . uniqid());
        $accessKey->setAppSecret('test-secret-' . uniqid());

        self::getEntityManager()->persist($accessKey);
        self::getEntityManager()->flush();

        $statistics = new AccessKeyStatistics();
        $statistics->setAccessKey($accessKey);
        $statistics->setHour(new \DateTimeImmutable('2023-10-15 ' . rand(0, 23) . ':00:00'));
        $statistics->setSuccessCount(rand(0, 100));
        $statistics->setFailureCount(rand(0, 20));

        return $statistics;
    }

    protected function getRepository(): AccessKeyStatisticsRepository
    {
        return self::getService(AccessKeyStatisticsRepository::class);
    }

    protected function onSetUp(): void
    {
    }

    public function testFindOrCreateForHourCreatesNew(): void
    {
        $accessKey = new AccessKey();
        $accessKey->setTitle('Test Key ' . uniqid());
        $accessKey->setAppId('test-app-' . uniqid());
        $accessKey->setAppSecret('test-secret-' . uniqid());

        self::getEntityManager()->persist($accessKey);
        self::getEntityManager()->flush();

        $hour = new \DateTimeImmutable('2023-10-15 14:30:45');
        $expectedHour = new \DateTimeImmutable('2023-10-15 14:00:00');

        $repository = $this->getRepository();
        $statistics = $repository->findOrCreateForHour($accessKey, $hour);

        $this->assertInstanceOf(AccessKeyStatistics::class, $statistics);
        $this->assertEquals($accessKey->getId(), $statistics->getAccessKey()->getId());
        $this->assertEquals($expectedHour, $statistics->getHour());
        $this->assertEquals(0, $statistics->getSuccessCount());
        $this->assertEquals(0, $statistics->getFailureCount());
    }

    public function testFindOrCreateForHourFindsExisting(): void
    {
        $accessKey = new AccessKey();
        $accessKey->setTitle('Test Key ' . uniqid());
        $accessKey->setAppId('test-app-' . uniqid());
        $accessKey->setAppSecret('test-secret-' . uniqid());

        self::getEntityManager()->persist($accessKey);
        self::getEntityManager()->flush();

        $hour = new \DateTimeImmutable('2023-10-15 14:00:00');

        $existing = new AccessKeyStatistics();
        $existing->setAccessKey($accessKey);
        $existing->setHour($hour);
        $existing->setSuccessCount(5);
        $existing->setFailureCount(1);

        self::getEntityManager()->persist($existing);
        self::getEntityManager()->flush();

        $repository = $this->getRepository();
        $statistics = $repository->findOrCreateForHour($accessKey, new \DateTimeImmutable('2023-10-15 14:30:45'));

        $this->assertInstanceOf(AccessKeyStatistics::class, $statistics);
        $this->assertEquals($existing->getId(), $statistics->getId());
        $this->assertEquals(5, $statistics->getSuccessCount());
        $this->assertEquals(1, $statistics->getFailureCount());
    }

    public function testGetSummaryByAccessKeyReturnsCorrectData(): void
    {
        $accessKey = new AccessKey();
        $accessKey->setTitle('Test Key ' . uniqid());
        $accessKey->setAppId('test-app-' . uniqid());
        $accessKey->setAppSecret('test-secret-' . uniqid());

        self::getEntityManager()->persist($accessKey);
        self::getEntityManager()->flush();

        $startDate = new \DateTimeImmutable('2023-10-15 00:00:00');
        $endDate = new \DateTimeImmutable('2023-10-15 23:59:59');

        $statistics1 = new AccessKeyStatistics();
        $statistics1->setAccessKey($accessKey);
        $statistics1->setHour(new \DateTimeImmutable('2023-10-15 14:00:00'));
        $statistics1->setSuccessCount(10);
        $statistics1->setFailureCount(2);

        $statistics2 = new AccessKeyStatistics();
        $statistics2->setAccessKey($accessKey);
        $statistics2->setHour(new \DateTimeImmutable('2023-10-15 15:00:00'));
        $statistics2->setSuccessCount(8);
        $statistics2->setFailureCount(1);

        self::getEntityManager()->persist($statistics1);
        self::getEntityManager()->persist($statistics2);
        self::getEntityManager()->flush();

        $repository = $this->getRepository();
        $summary = $repository->getSummaryByAccessKey($accessKey, $startDate, $endDate);

        $this->assertIsArray($summary);
        $this->assertEquals(18, $summary['totalSuccess']);
        $this->assertEquals(3, $summary['totalFailure']);
        $this->assertEquals(21, $summary['totalCalls']);
        $this->assertEqualsWithDelta(18 / 21, $summary['successRate'], 0.001);
    }

    public function testGetSummaryByAccessKeyReturnsZeroWhenNoData(): void
    {
        $accessKey = new AccessKey();
        $accessKey->setTitle('Test Key ' . uniqid());
        $accessKey->setAppId('test-app-' . uniqid());
        $accessKey->setAppSecret('test-secret-' . uniqid());

        self::getEntityManager()->persist($accessKey);
        self::getEntityManager()->flush();

        $startDate = new \DateTimeImmutable('2023-10-15 00:00:00');
        $endDate = new \DateTimeImmutable('2023-10-15 23:59:59');

        $repository = $this->getRepository();
        $summary = $repository->getSummaryByAccessKey($accessKey, $startDate, $endDate);

        $this->assertIsArray($summary);
        $this->assertEquals(0, $summary['totalSuccess']);
        $this->assertEquals(0, $summary['totalFailure']);
        $this->assertEquals(0, $summary['totalCalls']);
        $this->assertEquals(0.0, $summary['successRate']);
    }

    public function testFindStatisticsByAccessKeyAndDateRange(): void
    {
        $accessKey = new AccessKey();
        $accessKey->setTitle('Test Key ' . uniqid());
        $accessKey->setAppId('test-app-' . uniqid());
        $accessKey->setAppSecret('test-secret-' . uniqid());

        self::getEntityManager()->persist($accessKey);
        self::getEntityManager()->flush();

        $startDate = new \DateTimeImmutable('2023-10-15 00:00:00');
        $endDate = new \DateTimeImmutable('2023-10-15 23:59:59');

        $statistics1 = new AccessKeyStatistics();
        $statistics1->setAccessKey($accessKey);
        $statistics1->setHour(new \DateTimeImmutable('2023-10-15 14:00:00'));
        $statistics1->setSuccessCount(10);
        $statistics1->setFailureCount(2);

        $statistics2 = new AccessKeyStatistics();
        $statistics2->setAccessKey($accessKey);
        $statistics2->setHour(new \DateTimeImmutable('2023-10-15 15:00:00'));
        $statistics2->setSuccessCount(8);
        $statistics2->setFailureCount(1);

        self::getEntityManager()->persist($statistics1);
        self::getEntityManager()->persist($statistics2);
        self::getEntityManager()->flush();

        $repository = $this->getRepository();
        $results = $repository->findStatisticsByAccessKeyAndDateRange($accessKey, $startDate, $endDate);

        $this->assertCount(2, $results);
        $this->assertInstanceOf(AccessKeyStatistics::class, $results[0]);
        $this->assertInstanceOf(AccessKeyStatistics::class, $results[1]);

        $this->assertEquals(new \DateTimeImmutable('2023-10-15 14:00:00'), $results[0]->getHour());
        $this->assertEquals(new \DateTimeImmutable('2023-10-15 15:00:00'), $results[1]->getHour());
    }
}
