<?php

namespace Tourze\AccessKeyBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AccessKeyBundle\Entity\AccessKey;
use Tourze\AccessKeyBundle\Service\ApiCallerService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(ApiCallerService::class)]
#[RunTestsInSeparateProcesses]
final class ApiCallerServiceTest extends AbstractIntegrationTestCase
{
    private ApiCallerService $service;

    protected function onSetUp(): void
    {
        // Repository通过service获取，不需要单独存储
        $this->service = self::getService(ApiCallerService::class);
    }

    public function testFindValidApiCallerByAppId(): void
    {
        $appId = 'test-app-id';

        // 创建并持久化测试数据
        $apiCaller = new AccessKey();
        $apiCaller->setAppId($appId);
        $apiCaller->setTitle('Test API Caller');
        $apiCaller->setValid(true);

        $this->persistAndFlush($apiCaller);

        // 测试查找方法
        $result = $this->service->findValidApiCallerByAppId($appId);

        $this->assertInstanceOf(AccessKey::class, $result);
        $this->assertSame($appId, $result->getAppId());
        $this->assertTrue($result->isValid());
        $this->assertSame('Test API Caller', $result->getTitle());
    }

    public function testFindValidApiCallerByAppIdReturnsNull(): void
    {
        $appId = 'nonexistent-app-id';

        // 测试查找不存在的记录
        $result = $this->service->findValidApiCallerByAppId($appId);

        $this->assertNull($result);
    }

    public function testFindValidApiCallerByAppIdIgnoresInvalidCaller(): void
    {
        $appId = 'invalid-app-id';

        // 创建无效的API调用者
        $invalidApiCaller = new AccessKey();
        $invalidApiCaller->setAppId($appId);
        $invalidApiCaller->setTitle('Invalid API Caller');
        $invalidApiCaller->setValid(false); // 设置为无效

        $this->persistAndFlush($invalidApiCaller);

        // 测试查找方法应该返回null，因为API调用者无效
        $result = $this->service->findValidApiCallerByAppId($appId);

        $this->assertNull($result);
    }

    public function testFindValidApiCallerByAppIdWithValidityFiltering(): void
    {
        // 生成唯一的AppId避免冲突
        $validAppId = 'valid-test-app-id-' . uniqid();
        $invalidAppId = 'invalid-test-app-id-' . uniqid();

        // 创建一个无效的API调用者
        $invalidApiCaller = new AccessKey();
        $invalidApiCaller->setAppId($invalidAppId);
        $invalidApiCaller->setTitle('Invalid API Caller');
        $invalidApiCaller->setValid(false);

        // 创建一个有效的API调用者
        $validApiCaller = new AccessKey();
        $validApiCaller->setAppId($validAppId);
        $validApiCaller->setTitle('Valid API Caller');
        $validApiCaller->setValid(true);

        $this->persistAndFlush($invalidApiCaller);
        $this->persistAndFlush($validApiCaller);

        // 测试查找有效的API调用者
        $validResult = $this->service->findValidApiCallerByAppId($validAppId);
        $this->assertInstanceOf(AccessKey::class, $validResult);
        $this->assertSame($validAppId, $validResult->getAppId());
        $this->assertTrue($validResult->isValid());
        $this->assertSame('Valid API Caller', $validResult->getTitle());

        // 测试查找无效的API调用者应该返回null（因为findByAppId只返回valid=true的）
        $invalidResult = $this->service->findValidApiCallerByAppId($invalidAppId);
        $this->assertNull($invalidResult);
    }

    public function testFindValidApiCallerByAppSecret(): void
    {
        $appSecret = 'test-app-secret-' . uniqid();

        // 创建并持久化测试数据
        $apiCaller = new AccessKey();
        $apiCaller->setAppId('test-app-id-' . uniqid());
        $apiCaller->setAppSecret($appSecret);
        $apiCaller->setTitle('Test API Caller');
        $apiCaller->setValid(true);

        $this->persistAndFlush($apiCaller);

        // 测试查找方法
        $result = $this->service->findValidApiCallerByAppSecret($appSecret);

        $this->assertInstanceOf(AccessKey::class, $result);
        $this->assertSame($appSecret, $result->getAppSecret());
        $this->assertTrue($result->isValid());
        $this->assertSame('Test API Caller', $result->getTitle());
    }

    public function testFindValidApiCallerByAppSecretReturnsNull(): void
    {
        $appSecret = 'nonexistent-app-secret';

        // 测试查找不存在的记录
        $result = $this->service->findValidApiCallerByAppSecret($appSecret);

        $this->assertNull($result);
    }

    public function testFindValidApiCallerByAppSecretIgnoresInvalidCaller(): void
    {
        $appSecret = 'invalid-app-secret-' . uniqid();

        // 创建无效的API调用者
        $invalidApiCaller = new AccessKey();
        $invalidApiCaller->setAppId('invalid-app-id-' . uniqid());
        $invalidApiCaller->setAppSecret($appSecret);
        $invalidApiCaller->setTitle('Invalid API Caller');
        $invalidApiCaller->setValid(false); // 设置为无效

        $this->persistAndFlush($invalidApiCaller);

        // 测试查找方法应该返回null，因为API调用者无效
        $result = $this->service->findValidApiCallerByAppSecret($appSecret);

        $this->assertNull($result);
    }

    public function testFindValidApiCallerByAppSecretWithValidityFiltering(): void
    {
        // 生成唯一的AppSecret避免冲突
        $validAppSecret = 'valid-test-app-secret-' . uniqid();
        $invalidAppSecret = 'invalid-test-app-secret-' . uniqid();

        // 创建一个无效的API调用者
        $invalidApiCaller = new AccessKey();
        $invalidApiCaller->setAppId('invalid-test-app-id-' . uniqid());
        $invalidApiCaller->setAppSecret($invalidAppSecret);
        $invalidApiCaller->setTitle('Invalid API Caller');
        $invalidApiCaller->setValid(false);

        // 创建一个有效的API调用者
        $validApiCaller = new AccessKey();
        $validApiCaller->setAppId('valid-test-app-id-' . uniqid());
        $validApiCaller->setAppSecret($validAppSecret);
        $validApiCaller->setTitle('Valid API Caller');
        $validApiCaller->setValid(true);

        $this->persistAndFlush($invalidApiCaller);
        $this->persistAndFlush($validApiCaller);

        // 测试查找有效的API调用者
        $validResult = $this->service->findValidApiCallerByAppSecret($validAppSecret);
        $this->assertInstanceOf(AccessKey::class, $validResult);
        $this->assertSame($validAppSecret, $validResult->getAppSecret());
        $this->assertTrue($validResult->isValid());
        $this->assertSame('Valid API Caller', $validResult->getTitle());

        // 测试查找无效的API调用者应该返回null（因为findValidApiCallerByAppSecret只返回valid=true的）
        $invalidResult = $this->service->findValidApiCallerByAppSecret($invalidAppSecret);
        $this->assertNull($invalidResult);
    }

    public function testRecordSuccess(): void
    {
        $appId = 'success-test-app-id';

        // 创建测试API调用者
        $apiCaller = new AccessKey();
        $apiCaller->setAppId($appId);
        $apiCaller->setTitle('Success Test API Caller');
        $apiCaller->setValid(true);

        $this->persistAndFlush($apiCaller);

        // 测试记录成功调用
        $this->service->recordSuccess($apiCaller);

        // 验证方法调用不抛出异常
        $this->assertTrue(true); // 如果执行到这里说明没有异常
    }

    public function testRecordFailure(): void
    {
        $appId = 'failure-test-app-id';

        // 创建测试API调用者
        $apiCaller = new AccessKey();
        $apiCaller->setAppId($appId);
        $apiCaller->setTitle('Failure Test API Caller');
        $apiCaller->setValid(true);

        $this->persistAndFlush($apiCaller);

        // 测试记录失败调用
        $this->service->recordFailure($apiCaller);

        // 验证方法调用不抛出异常
        $this->assertTrue(true); // 如果执行到这里说明没有异常
    }
}
