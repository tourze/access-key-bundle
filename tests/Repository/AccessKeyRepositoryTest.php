<?php

namespace Tourze\AccessKeyBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Persisters\Exception\UnrecognizedField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AccessKeyBundle\Entity\AccessKey;
use Tourze\AccessKeyBundle\Repository\AccessKeyRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(AccessKeyRepository::class)]
#[RunTestsInSeparateProcesses]
final class AccessKeyRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        $repository = self::getService(AccessKeyRepository::class);

        // 创建测试数据以满足基类要求
        $entity = new AccessKey();
        $entity->setTitle('Setup Test API Caller');
        $entity->setAppId('setup-test-app-id');
        $entity->setAppSecret('setup-test-secret');
        $entity->setValid(true);
        $repository->save($entity, true);
    }

    public function testSaveMethod(): void
    {
        $repository = self::getService(AccessKeyRepository::class);

        $entity = new AccessKey();
        $entity->setTitle('Test API');
        $entity->setAppId('test-app-id');
        $entity->setAppSecret('test-secret');

        $repository->save($entity);

        $this->assertNotNull($entity->getId());
    }

    public function testRemoveMethod(): void
    {
        $repository = self::getService(AccessKeyRepository::class);

        $entity = new AccessKey();
        $entity->setTitle('Test API');
        $entity->setAppId('test-app-id');
        $entity->setAppSecret('test-secret');

        $repository->save($entity);
        $id = $entity->getId();

        $repository->remove($entity);

        $found = $repository->find($id);
        $this->assertNull($found);
    }

    public function testFindByNullValueQuery(): void
    {
        $repository = self::getService(AccessKeyRepository::class);

        // 创建一个包含 null 值的实体
        $entity = new AccessKey();
        $entity->setTitle('Test API');
        $entity->setAppId('test-app-id');
        $entity->setAppSecret('test-secret');
        // remark 字段允许为 null

        $repository->save($entity);

        // 查询 remark 为 null 的实体
        $results = $repository->findBy(['remark' => null]);
        $this->assertGreaterThanOrEqual(1, count($results));
    }

    public function testCountByNullValueQuery(): void
    {
        $repository = self::getService(AccessKeyRepository::class);

        // 创建一个包含 null 值的实体
        $entity = new AccessKey();
        $entity->setTitle('Test API');
        $entity->setAppId('test-app-id');
        $entity->setAppSecret('test-secret');

        $repository->save($entity);

        // 统计 remark 为 null 的实体数量
        $count = $repository->count(['remark' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByWithSortingReturnsFirstResult(): void
    {
        $repository = self::getService(AccessKeyRepository::class);

        // 使用唯一的remark来标识我们的测试数据
        $uniqueRemark = 'sort-test-' . uniqid();

        // 创建两个测试实体
        $entity1 = new AccessKey();
        $entity1->setTitle('Sort Test API 1 ' . uniqid());
        $entity1->setAppId('sort-app-id-1-' . uniqid());
        $entity1->setAppSecret('sort-secret-1');
        $entity1->setRemark($uniqueRemark);
        $repository->save($entity1);

        $entity2 = new AccessKey();
        $entity2->setTitle('Sort Test API 2 ' . uniqid());
        $entity2->setAppId('sort-app-id-2-' . uniqid());
        $entity2->setAppSecret('sort-secret-2');
        $entity2->setRemark($uniqueRemark);
        $repository->save($entity2);

        // 测试排序后的第一个结果 - 只查询我们的测试数据
        $results = $repository->findBy(['remark' => $uniqueRemark], ['appId' => 'ASC']);
        $this->assertCount(2, $results);
        $this->assertInstanceOf(AccessKey::class, $results[0]);

        // 验证排序是否正确
        $firstAppId = $results[0]->getAppId();
        $secondAppId = $results[1]->getAppId();
        $this->assertLessThan($secondAppId, $firstAppId);
    }

    public function testFindByNullFieldQueries(): void
    {
        $repository = self::getService(AccessKeyRepository::class);

        // 创建一个有null字段的实体
        $entity = new AccessKey();
        $entity->setTitle('Null Test API');
        $entity->setAppId('null-app-id');
        $entity->setAppSecret('null-secret');
        $entity->setRemark(null); // 设置为null
        $repository->save($entity);

        // 测试IS NULL查询
        $results = $repository->findBy(['remark' => null]);
        $this->assertGreaterThanOrEqual(1, count($results));

        foreach ($results as $result) {
            $this->assertNull($result->getRemark());
        }
    }

    public function testCountByNullFieldQueries(): void
    {
        $repository = self::getService(AccessKeyRepository::class);

        // 创建一个有null字段的实体
        $entity = new AccessKey();
        $entity->setTitle('Count Null Test API');
        $entity->setAppId('count-null-app-id');
        $entity->setAppSecret('count-null-secret');
        $entity->setAesKey(null); // 设置为null
        $repository->save($entity);

        // 测试IS NULL计数查询
        $count = $repository->count(['aesKey' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testRepositoryRobustnessWithComplexInvalidQueries(): void
    {
        $repository = self::getService(AccessKeyRepository::class);

        // 测试使用多个无效字段的健壮性
        $this->expectException(UnrecognizedField::class);
        $repository->count(['nonExistentField1' => 'value1', 'nonExistentField2' => 'value2']);
    }

    public function testIsNullQueryForAllowIpsField(): void
    {
        $repository = self::getService(AccessKeyRepository::class);

        // 创建一个allowIps为null的实体
        $entity = new AccessKey();
        $entity->setTitle('Null AllowIps Test API');
        $entity->setAppId('null-allowips-app-id');
        $entity->setAppSecret('null-allowips-secret');
        $entity->setAllowIps(null);
        $repository->save($entity);

        // 测试IS NULL查询allowIps字段
        $results = $repository->findBy(['allowIps' => null]);
        $this->assertGreaterThanOrEqual(1, count($results));

        foreach ($results as $result) {
            $this->assertNull($result->getAllowIps());
        }
    }

    public function testIsNullQueryForSignTimeoutSecondField(): void
    {
        $repository = self::getService(AccessKeyRepository::class);

        // 创建一个signTimeoutSecond为null的实体
        $entity = new AccessKey();
        $entity->setTitle('Null SignTimeout Test API');
        $entity->setAppId('null-signtimeout-app-id');
        $entity->setAppSecret('null-signtimeout-secret');
        $entity->setSignTimeoutSecond(null);
        $repository->save($entity);

        // 测试IS NULL查询signTimeoutSecond字段
        $results = $repository->findBy(['signTimeoutSecond' => null]);
        $this->assertGreaterThanOrEqual(1, count($results));

        foreach ($results as $result) {
            $this->assertNull($result->getSignTimeoutSecond());
        }
    }

    public function testFindByAppIdReturnsValidAccessKey(): void
    {
        $repository = self::getService(AccessKeyRepository::class);

        // 生成唯一的AppId避免冲突
        $uniqueAppId = 'valid-app-id-' . uniqid();

        // 创建一个有效的AccessKey
        $validEntity = new AccessKey();
        $validEntity->setTitle('Valid API Key');
        $validEntity->setAppId($uniqueAppId);
        $validEntity->setAppSecret('valid-secret');
        $validEntity->setValid(true);
        $repository->save($validEntity);

        // 测试查找有效的AccessKey
        $found = $repository->findByAppId($uniqueAppId);
        $this->assertNotNull($found);
        $this->assertEquals($uniqueAppId, $found->getAppId());
        $this->assertTrue($found->isValid());
        $this->assertEquals('Valid API Key', $found->getTitle());
    }

    public function testFindByAppIdReturnsNullForNonExistentAppId(): void
    {
        $repository = self::getService(AccessKeyRepository::class);

        $found = $repository->findByAppId('non-existent-app-id');
        $this->assertNull($found);
    }

    public function testFindByAppIdReturnsNullForInvalidAppId(): void
    {
        $repository = self::getService(AccessKeyRepository::class);

        // 创建一个无效的AccessKey
        $invalidEntity = new AccessKey();
        $invalidEntity->setTitle('Invalid API Key');
        $invalidEntity->setAppId('invalid-app-id');
        $invalidEntity->setAppSecret('invalid-secret');
        $invalidEntity->setValid(false);
        $repository->save($invalidEntity);

        // 测试查找无效的AccessKey应该返回null
        $found = $repository->findByAppId('invalid-app-id');
        $this->assertNull($found);
    }

    protected function createNewEntity(): object
    {
        $entity = new AccessKey();
        $entity->setTitle('Test API ' . uniqid());
        $entity->setAppId('test-app-id-' . uniqid());
        $entity->setAppSecret('test-secret-' . uniqid());

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<AccessKey>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(AccessKeyRepository::class);
    }
}
