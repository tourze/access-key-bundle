<?php

declare(strict_types=1);

namespace Tourze\AccessKeyBundle\Interface;

use Tourze\AccessKeyBundle\Entity\AccessKey;

/**
 * AccessKey查找服务接口
 */
interface AccessKeyFinderInterface
{
    /**
     * 根据ID查找AccessKey
     *
     * @throws \InvalidArgumentException 当AccessKey不存在时
     */
    public function findRequiredById(int|string $accessKeyId): AccessKey;

    /**
     * 根据ID查找AccessKey，可能返回null
     */
    public function findById(int|string $accessKeyId): ?AccessKey;
}
