<?php

namespace Tourze\AccessKeyBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\AccessKeyBundle\Entity\AccessKey;
use Tourze\AccessKeyBundle\Repository\AccessKeyRepository;

#[Autoconfigure(public: true)]
readonly class ApiCallerService
{
    public function __construct(
        private AccessKeyRepository $apiCallerRepository,
        private StatisticsService $statisticsService,
    ) {
    }

    public function findValidApiCallerByAppId(string $appId): ?AccessKey
    {
        return $this->apiCallerRepository->findOneBy([
            'appId' => $appId,
            'valid' => true,
        ]);
    }

    public function findValidApiCallerByAppSecret(string $appSecret): ?AccessKey
    {
        return $this->apiCallerRepository->findOneBy([
            'appSecret' => $appSecret,
            'valid' => true,
        ]);
    }

    public function recordSuccess(AccessKey $accessKey, ?\DateTimeImmutable $time = null): void
    {
        $this->statisticsService->incrementSuccess($accessKey, $time);
    }

    public function recordFailure(AccessKey $accessKey, ?\DateTimeImmutable $time = null): void
    {
        $this->statisticsService->incrementFailure($accessKey, $time);
    }
}
