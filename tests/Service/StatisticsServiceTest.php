<?php

namespace Tourze\AccessKeyBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AccessKeyBundle\Entity\AccessKey;
use Tourze\AccessKeyBundle\Entity\AccessKeyStatistics;
use Tourze\AccessKeyBundle\Repository\AccessKeyStatisticsRepository;
use Tourze\AccessKeyBundle\Service\StatisticsService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(StatisticsService::class)]
#[RunTestsInSeparateProcesses]
final class StatisticsServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
    }

    public function testGetHourlyStatisticsReturnsFormattedData(): void
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

        $service = self::getService(StatisticsService::class);
        $result = $service->getHourlyStatistics($accessKey, $startDate, $endDate);

        $expected = [
            [
                'hour' => '2023-10-15 14:00:00',
                'successCount' => 10,
                'failureCount' => 2,
                'totalCount' => 12,
                'successRate' => 10 / 12,
            ],
            [
                'hour' => '2023-10-15 15:00:00',
                'successCount' => 8,
                'failureCount' => 1,
                'totalCount' => 9,
                'successRate' => 8 / 9,
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testIncrementSuccess(): void
    {
        $accessKey = new AccessKey();
        $accessKey->setTitle('Test Key ' . uniqid());
        $accessKey->setAppId('test-app-' . uniqid());
        $accessKey->setAppSecret('test-secret-' . uniqid());

        self::getEntityManager()->persist($accessKey);
        self::getEntityManager()->flush();
        $time = new \DateTimeImmutable('2023-10-15 14:30:00');

        $service = self::getService(StatisticsService::class);
        $service->incrementSuccess($accessKey, $time);

        $repository = self::getService(AccessKeyStatisticsRepository::class);
        $statistics = $repository->findOneBy([
            'accessKey' => $accessKey,
            'hour' => new \DateTimeImmutable('2023-10-15 14:00:00'),
        ]);

        $this->assertInstanceOf(AccessKeyStatistics::class, $statistics);
        $this->assertEquals(1, $statistics->getSuccessCount());
        $this->assertEquals(0, $statistics->getFailureCount());
    }

    public function testIncrementFailure(): void
    {
        $accessKey = new AccessKey();
        $accessKey->setTitle('Test Key ' . uniqid());
        $accessKey->setAppId('test-app-' . uniqid());
        $accessKey->setAppSecret('test-secret-' . uniqid());

        self::getEntityManager()->persist($accessKey);
        self::getEntityManager()->flush();
        $time = new \DateTimeImmutable('2023-10-15 14:30:00');

        $service = self::getService(StatisticsService::class);
        $service->incrementFailure($accessKey, $time);

        $repository = self::getService(AccessKeyStatisticsRepository::class);
        $statistics = $repository->findOneBy([
            'accessKey' => $accessKey,
            'hour' => new \DateTimeImmutable('2023-10-15 14:00:00'),
        ]);

        $this->assertInstanceOf(AccessKeyStatistics::class, $statistics);
        $this->assertEquals(0, $statistics->getSuccessCount());
        $this->assertEquals(1, $statistics->getFailureCount());
    }

    public function testGetSummary(): void
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
        $statistics1->setSuccessCount(50);
        $statistics1->setFailureCount(10);

        $statistics2 = new AccessKeyStatistics();
        $statistics2->setAccessKey($accessKey);
        $statistics2->setHour(new \DateTimeImmutable('2023-10-15 15:00:00'));
        $statistics2->setSuccessCount(30);
        $statistics2->setFailureCount(5);

        self::getEntityManager()->persist($statistics1);
        self::getEntityManager()->persist($statistics2);
        self::getEntityManager()->flush();

        $service = self::getService(StatisticsService::class);
        $result = $service->getSummary($accessKey, $startDate, $endDate);

        $this->assertIsArray($result);
        $this->assertEquals(80, $result['totalSuccess']);
        $this->assertEquals(15, $result['totalFailure']);
        $this->assertEquals(95, $result['totalCalls']);
        $this->assertEqualsWithDelta(80 / 95, $result['successRate'], 0.001);
    }

    public function testGetTodayStatistics(): void
    {
        $accessKey = new AccessKey();
        $accessKey->setTitle('Test Key ' . uniqid());
        $accessKey->setAppId('test-app-' . uniqid());
        $accessKey->setAppSecret('test-secret-' . uniqid());

        self::getEntityManager()->persist($accessKey);
        self::getEntityManager()->flush();

        $today = new \DateTimeImmutable();
        $todayStart = $today->setTime(0, 0, 0);

        $statistics = new AccessKeyStatistics();
        $statistics->setAccessKey($accessKey);
        $statistics->setHour($todayStart->setTime(14, 0, 0));
        $statistics->setSuccessCount(25);
        $statistics->setFailureCount(5);

        self::getEntityManager()->persist($statistics);
        self::getEntityManager()->flush();

        $service = self::getService(StatisticsService::class);
        $result = $service->getTodayStatistics($accessKey);

        $this->assertIsArray($result);
        $this->assertEquals(25, $result['totalSuccess']);
        $this->assertEquals(5, $result['totalFailure']);
        $this->assertEquals(30, $result['totalCalls']);
        $this->assertEqualsWithDelta(25 / 30, $result['successRate'], 0.001);
    }

    public function testGetWeeklyStatistics(): void
    {
        $accessKey = new AccessKey();
        $accessKey->setTitle('Test Key ' . uniqid());
        $accessKey->setAppId('test-app-' . uniqid());
        $accessKey->setAppSecret('test-secret-' . uniqid());

        self::getEntityManager()->persist($accessKey);
        self::getEntityManager()->flush();

        $today = new \DateTimeImmutable();
        $weekStart = $today->modify('monday this week')->setTime(0, 0, 0);

        $statistics = new AccessKeyStatistics();
        $statistics->setAccessKey($accessKey);
        $statistics->setHour($weekStart->setTime(14, 0, 0));
        $statistics->setSuccessCount(150);
        $statistics->setFailureCount(30);

        self::getEntityManager()->persist($statistics);
        self::getEntityManager()->flush();

        $service = self::getService(StatisticsService::class);
        $result = $service->getWeeklyStatistics($accessKey);

        $this->assertIsArray($result);
        $this->assertEquals(150, $result['totalSuccess']);
        $this->assertEquals(30, $result['totalFailure']);
        $this->assertEquals(180, $result['totalCalls']);
        $this->assertEqualsWithDelta(150 / 180, $result['successRate'], 0.001);
    }

    public function testGetMonthlyStatistics(): void
    {
        $accessKey = new AccessKey();
        $accessKey->setTitle('Test Key ' . uniqid());
        $accessKey->setAppId('test-app-' . uniqid());
        $accessKey->setAppSecret('test-secret-' . uniqid());

        self::getEntityManager()->persist($accessKey);
        self::getEntityManager()->flush();

        $today = new \DateTimeImmutable();
        $monthStart = $today->modify('first day of this month')->setTime(0, 0, 0);

        $statistics = new AccessKeyStatistics();
        $statistics->setAccessKey($accessKey);
        $statistics->setHour($monthStart->setTime(14, 0, 0));
        $statistics->setSuccessCount(600);
        $statistics->setFailureCount(120);

        self::getEntityManager()->persist($statistics);
        self::getEntityManager()->flush();

        $service = self::getService(StatisticsService::class);
        $result = $service->getMonthlyStatistics($accessKey);

        $this->assertIsArray($result);
        $this->assertEquals(600, $result['totalSuccess']);
        $this->assertEquals(120, $result['totalFailure']);
        $this->assertEquals(720, $result['totalCalls']);
        $this->assertEqualsWithDelta(600 / 720, $result['successRate'], 0.001);
    }
}
