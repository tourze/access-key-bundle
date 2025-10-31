<?php

namespace Tourze\AccessKeyBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\AccessKeyBundle\Entity\AccessKey;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(AccessKey::class)]
final class AccessKeyTest extends AbstractEntityTestCase
{
    private AccessKey $apiCaller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiCaller = new AccessKey();
    }

    /**
     * 创建被测实体的一个实例.
     */
    protected function createEntity(): object
    {
        return new AccessKey();
    }

    /**
     * 提供属性及其样本值的 Data Provider.
     *
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'appId' => ['appId', 'test-app-id'],
            'appSecret' => ['appSecret', 'test-app-secret'],
            'title' => ['title', '测试应用'],
            'allowIps' => ['allowIps', ['127.0.0.1', '192.168.1.1']],
            'signTimeoutSecond' => ['signTimeoutSecond', 300],
            'aesKey' => ['aesKey', 'test-aes-key'],
            'remark' => ['remark', '测试备注'],
            'valid' => ['valid', true],
            'createdBy' => ['createdBy', 'admin-user'],
            'updatedBy' => ['updatedBy', 'admin-user'],
            'createTime' => ['createTime', new \DateTimeImmutable('2023-01-01 12:00:00')],
            'updateTime' => ['updateTime', new \DateTimeImmutable('2023-01-02 12:00:00')],
        ];
    }

    public function testSetAndGetAppIdWithValidData(): void
    {
        $appId = 'test-app-id';
        $this->apiCaller->setAppId($appId);
        $this->assertEquals($appId, $this->apiCaller->getAppId());
    }

    public function testSetAndGetAppSecretWithValidData(): void
    {
        $appSecret = 'test-app-secret';
        $this->apiCaller->setAppSecret($appSecret);
        $this->assertEquals($appSecret, $this->apiCaller->getAppSecret());
    }

    public function testSetAndGetTitleWithValidData(): void
    {
        $title = '测试应用';
        $this->apiCaller->setTitle($title);
        $this->assertEquals($title, $this->apiCaller->getTitle());
    }

    public function testSetAndGetAllowIpsWithValidData(): void
    {
        $allowIps = ['127.0.0.1', '192.168.1.1'];
        $this->apiCaller->setAllowIps($allowIps);
        $this->assertEquals($allowIps, $this->apiCaller->getAllowIps());
    }

    public function testSetAndGetSignTimeoutSecondWithValidData(): void
    {
        $timeout = 300;
        $this->apiCaller->setSignTimeoutSecond($timeout);
        $this->assertEquals($timeout, $this->apiCaller->getSignTimeoutSecond());
    }

    public function testSetAndGetAesKeyWithValidData(): void
    {
        $aesKey = 'test-aes-key';
        $this->apiCaller->setAesKey($aesKey);
        $this->assertEquals($aesKey, $this->apiCaller->getAesKey());
    }

    public function testSetAndGetRemarkWithValidData(): void
    {
        $remark = '测试备注';
        $this->apiCaller->setRemark($remark);
        $this->assertEquals($remark, $this->apiCaller->getRemark());
    }

    public function testSetAndGetValidWithValidData(): void
    {
        $valid = true;
        $this->apiCaller->setValid($valid);
        $this->assertEquals($valid, $this->apiCaller->isValid());
    }

    public function testSetAndGetCreatedByWithValidData(): void
    {
        $createdBy = 'admin-user';
        $this->apiCaller->setCreatedBy($createdBy);
        $this->assertEquals($createdBy, $this->apiCaller->getCreatedBy());
    }

    public function testSetAndGetUpdatedByWithValidData(): void
    {
        $updatedBy = 'admin-user';
        $this->apiCaller->setUpdatedBy($updatedBy);
        $this->assertEquals($updatedBy, $this->apiCaller->getUpdatedBy());
    }

    public function testSetAndGetCreateTimeWithValidData(): void
    {
        $createTime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $this->apiCaller->setCreateTime($createTime);
        $this->assertEquals($createTime, $this->apiCaller->getCreateTime());
    }

    public function testSetAndGetUpdateTimeWithValidData(): void
    {
        $updateTime = new \DateTimeImmutable('2023-01-02 12:00:00');
        $this->apiCaller->setUpdateTime($updateTime);
        $this->assertEquals($updateTime, $this->apiCaller->getUpdateTime());
    }

    public function testSetAndGetIdReturnsNullByDefault(): void
    {
        // ID 默认情况下应为 null（由 Doctrine 自动生成）
        $this->assertNull($this->apiCaller->getId());
    }

    public function testSetAndGetOwnerWithValidData(): void
    {
        $owner = $this->createMock(UserInterface::class);
        $owner->method('getUserIdentifier')->willReturn('test-user');
        $owner->method('getRoles')->willReturn(['ROLE_USER']);

        $this->apiCaller->setOwner($owner);
        $this->assertSame($owner, $this->apiCaller->getOwner());
    }

    public function testSetOwnerWithNullValue(): void
    {
        $this->apiCaller->setOwner(null);
        $this->assertNull($this->apiCaller->getOwner());
    }

    public function testOwnerDefaultValueIsNull(): void
    {
        $this->assertNull($this->apiCaller->getOwner());
    }
}
