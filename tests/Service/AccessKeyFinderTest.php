<?php

namespace Tourze\AccessKeyBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AccessKeyBundle\Entity\AccessKey;
use Tourze\AccessKeyBundle\Service\AccessKeyFinder;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(AccessKeyFinder::class)]
#[RunTestsInSeparateProcesses]
final class AccessKeyFinderTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
    }

    public function testFindRequiredByIdReturnsAccessKeyWhenFound(): void
    {
        $accessKey = new AccessKey();
        $accessKey->setTitle('Test Key ' . uniqid());
        $accessKey->setAppId('test-app-' . uniqid());
        $accessKey->setAppSecret('test-secret-' . uniqid());

        self::getEntityManager()->persist($accessKey);
        self::getEntityManager()->flush();

        $finder = self::getService(AccessKeyFinder::class);
        $accessKeyId = $accessKey->getId();
        $this->assertNotNull($accessKeyId);

        $result = $finder->findRequiredById($accessKeyId);

        $this->assertInstanceOf(AccessKey::class, $result);
        $this->assertEquals($accessKey->getId(), $result->getId());
        $this->assertEquals($accessKey->getTitle(), $result->getTitle());
    }

    public function testFindRequiredByIdThrowsExceptionWhenNotFound(): void
    {
        $finder = self::getService(AccessKeyFinder::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('AccessKey not found: 999999');

        $finder->findRequiredById(999999);
    }

    public function testFindByIdReturnsAccessKeyWhenFound(): void
    {
        $accessKey = new AccessKey();
        $accessKey->setTitle('Test Key ' . uniqid());
        $accessKey->setAppId('test-app-' . uniqid());
        $accessKey->setAppSecret('test-secret-' . uniqid());

        self::getEntityManager()->persist($accessKey);
        self::getEntityManager()->flush();

        $finder = self::getService(AccessKeyFinder::class);
        $accessKeyId = $accessKey->getId();
        $this->assertNotNull($accessKeyId);

        $result = $finder->findById($accessKeyId);

        $this->assertInstanceOf(AccessKey::class, $result);
        $this->assertEquals($accessKey->getId(), $result->getId());
        $this->assertEquals($accessKey->getTitle(), $result->getTitle());
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $finder = self::getService(AccessKeyFinder::class);
        $result = $finder->findById(999999);

        $this->assertNull($result);
    }

    public function testFindRequiredByIdWithStringId(): void
    {
        $accessKey = new AccessKey();
        $accessKey->setTitle('Test Key ' . uniqid());
        $accessKey->setAppId('test-app-' . uniqid());
        $accessKey->setAppSecret('test-secret-' . uniqid());

        self::getEntityManager()->persist($accessKey);
        self::getEntityManager()->flush();

        $finder = self::getService(AccessKeyFinder::class);
        $accessKeyId = $accessKey->getId();
        $this->assertNotNull($accessKeyId);

        $result = $finder->findRequiredById($accessKeyId);

        $this->assertInstanceOf(AccessKey::class, $result);
        $this->assertEquals($accessKey->getId(), $result->getId());
    }

    public function testFindByIdWithStringId(): void
    {
        $accessKey = new AccessKey();
        $accessKey->setTitle('Test Key ' . uniqid());
        $accessKey->setAppId('test-app-' . uniqid());
        $accessKey->setAppSecret('test-secret-' . uniqid());

        self::getEntityManager()->persist($accessKey);
        self::getEntityManager()->flush();

        $finder = self::getService(AccessKeyFinder::class);
        $accessKeyId = $accessKey->getId();
        $this->assertNotNull($accessKeyId);

        $result = $finder->findById($accessKeyId);

        $this->assertInstanceOf(AccessKey::class, $result);
        $this->assertEquals($accessKey->getId(), $result->getId());
    }
}
