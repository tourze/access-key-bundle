<?php

namespace Tourze\AccessKeyBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\AccessKeyBundle\Entity\AccessKey;
use Tourze\AccessKeyBundle\Repository\AccessKeyStatisticsRepository;

#[Autoconfigure(public: true)]
readonly class StatisticsService
{
    public function __construct(private AccessKeyStatisticsRepository $statisticsRepository)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getHourlyStatistics(AccessKey $accessKey, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        $statistics = $this->statisticsRepository->findStatisticsByAccessKeyAndDateRange($accessKey, $startDate, $endDate);

        $hourlyData = [];
        foreach ($statistics as $stat) {
            $hourlyData[] = [
                'hour' => $stat->getHour()->format('Y-m-d H:00:00'),
                'successCount' => $stat->getSuccessCount(),
                'failureCount' => $stat->getFailureCount(),
                'totalCount' => $stat->getTotalCount(),
                'successRate' => $stat->getSuccessRate(),
            ];
        }

        return $hourlyData;
    }

    /**
     * @return array<string, mixed>
     */
    public function getTodayStatistics(AccessKey $accessKey): array
    {
        $today = new \DateTimeImmutable('today');
        $tomorrow = $today->modify('+1 day');

        return $this->getSummary($accessKey, $today, $tomorrow);
    }

    /**
     * @return array<string, mixed>
     */
    public function getSummary(AccessKey $accessKey, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        return $this->statisticsRepository->getSummaryByAccessKey($accessKey, $startDate, $endDate);
    }

    /**
     * @return array<string, mixed>
     */
    public function getWeeklyStatistics(AccessKey $accessKey): array
    {
        $weekStart = (new \DateTimeImmutable('monday this week'))->setTime(0, 0, 0);
        $weekEnd = $weekStart->modify('+7 days');

        return $this->getSummary($accessKey, $weekStart, $weekEnd);
    }

    /**
     * @return array<string, mixed>
     */
    public function getMonthlyStatistics(AccessKey $accessKey): array
    {
        $monthStart = (new \DateTimeImmutable('first day of this month'))->setTime(0, 0, 0);
        $monthEnd = $monthStart->modify('+1 month');

        return $this->getSummary($accessKey, $monthStart, $monthEnd);
    }

    public function incrementSuccess(AccessKey $accessKey, ?\DateTimeImmutable $time = null): void
    {
        $time ??= new \DateTimeImmutable();
        $statistics = $this->statisticsRepository->findOrCreateForHour($accessKey, $time);
        $statistics->incrementSuccess();
        $this->statisticsRepository->save($statistics);
    }

    public function incrementFailure(AccessKey $accessKey, ?\DateTimeImmutable $time = null): void
    {
        $time ??= new \DateTimeImmutable();
        $statistics = $this->statisticsRepository->findOrCreateForHour($accessKey, $time);
        $statistics->incrementFailure();
        $this->statisticsRepository->save($statistics);
    }
}
