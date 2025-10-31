<?php

namespace Tourze\AccessKeyBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\AccessKeyBundle\Entity\AccessKey;
use Tourze\AccessKeyBundle\Entity\AccessKeyStatistics;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(AccessKeyStatistics::class)]
final class AccessKeyStatisticsTest extends AbstractEntityTestCase
{
    private AccessKey $accessKey;

    private AccessKeyStatistics $statistics;

    public function testIncrementSuccess(): void
    {
        $this->assertEquals(0, $this->statistics->getSuccessCount());

        $this->statistics->incrementSuccess();
        $this->assertEquals(1, $this->statistics->getSuccessCount());

        $this->statistics->incrementSuccess();
        $this->assertEquals(2, $this->statistics->getSuccessCount());
    }

    public function testIncrementFailure(): void
    {
        $this->assertEquals(0, $this->statistics->getFailureCount());

        $this->statistics->incrementFailure();
        $this->assertEquals(1, $this->statistics->getFailureCount());

        $this->statistics->incrementFailure();
        $this->assertEquals(2, $this->statistics->getFailureCount());
    }

    public function testGetTotalCount(): void
    {
        $this->statistics->setSuccessCount(5);
        $this->statistics->setFailureCount(3);

        $this->assertEquals(8, $this->statistics->getTotalCount());
    }

    public function testGetSuccessRateWithCalls(): void
    {
        $this->statistics->setSuccessCount(7);
        $this->statistics->setFailureCount(3);

        $this->assertEquals(0.7, $this->statistics->getSuccessRate());
    }

    public function testGetSuccessRateWithoutCalls(): void
    {
        $this->assertEquals(0.0, $this->statistics->getSuccessRate());
    }

    public function testGetSuccessRateWithOnlyFailures(): void
    {
        $this->statistics->setFailureCount(5);

        $this->assertEquals(0.0, $this->statistics->getSuccessRate());
    }

    public function testGetSuccessRateWithOnlySuccess(): void
    {
        $this->statistics->setSuccessCount(5);

        $this->assertEquals(1.0, $this->statistics->getSuccessRate());
    }

    /**
     * 提供属性及其样本值的 Data Provider.
     *
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        $accessKey = new AccessKey();
        $accessKey->setTitle('Test Key');
        $accessKey->setAppId('test-app-id');

        return [
            'accessKey' => ['accessKey', $accessKey],
            'hour' => ['hour', new \DateTimeImmutable('2023-10-15 14:00:00')],
            'successCount' => ['successCount', 10],
            'failureCount' => ['failureCount', 5],
        ];
    }

    protected function createEntity(): AccessKeyStatistics
    {
        $accessKey = new AccessKey();
        $accessKey->setTitle('Test Key');
        $accessKey->setAppId('test-app-id');

        $statistics = new AccessKeyStatistics();
        $statistics->setAccessKey($accessKey);
        $statistics->setHour(new \DateTimeImmutable('2023-10-15 14:00:00'));

        return $statistics;
    }

    protected function setUp(): void
    {
        $this->accessKey = new AccessKey();
        $this->accessKey->setTitle('Test Key');
        $this->accessKey->setAppId('test-app-id');

        $this->statistics = new AccessKeyStatistics();
        $this->statistics->setAccessKey($this->accessKey);
        $this->statistics->setHour(new \DateTimeImmutable('2023-10-15 14:00:00'));
    }
}
