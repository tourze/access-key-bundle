<?php

declare(strict_types=1);

namespace Tourze\AccessKeyBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\AccessKeyBundle\Entity\AccessKey;
use Tourze\AccessKeyBundle\Interface\AccessKeyFinderInterface;
use Tourze\AccessKeyBundle\Repository\AccessKeyRepository;

/**
 * AccessKey查找服务
 */
#[Autoconfigure(public: true)]
#[AsAlias(id: AccessKeyFinderInterface::class)]
final readonly class AccessKeyFinder implements AccessKeyFinderInterface
{
    public function __construct(
        private AccessKeyRepository $accessKeyRepository,
    ) {
    }

    public function findRequiredById(int|string $accessKeyId): AccessKey
    {
        $accessKey = $this->accessKeyRepository->find($accessKeyId);
        if (null === $accessKey) {
            throw new \InvalidArgumentException("AccessKey not found: {$accessKeyId}");
        }

        return $accessKey;
    }

    public function findById(int|string $accessKeyId): ?AccessKey
    {
        return $this->accessKeyRepository->find($accessKeyId);
    }
}
