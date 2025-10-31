<?php

namespace Tourze\AccessKeyBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\AccessKeyBundle\Entity\AccessKey;

/**
 * ApiCaller 数据夹具
 *
 * 此夹具创建多个 JSON-RPC 调用者，包括：
 * - 默认调用者：基本配置，有效
 * - 自定义超时调用者：长超时时间
 * - IP白名单调用者：配置IP白名单
 * - 带AES密钥调用者：配置了加密密钥
 * - 无效调用者：标记为无效状态
 */
final class AccessKeyFixtures extends Fixture implements FixtureGroupInterface
{
    // 定义引用常量
    public const DEFAULT_CALLER_REFERENCE = 'default-api-caller';
    public const LONG_TIMEOUT_CALLER_REFERENCE = 'long-timeout-api-caller';
    public const IP_RESTRICTED_CALLER_REFERENCE = 'ip-restricted-api-caller';
    public const AES_ENABLED_CALLER_REFERENCE = 'aes-enabled-api-caller';
    public const INVALID_CALLER_REFERENCE = 'invalid-api-caller';

    /**
     * 返回此fixture所属的组名
     */
    public static function getGroups(): array
    {
        return ['json-rpc-caller', 'api', 'test'];
    }

    public function load(ObjectManager $manager): void
    {
        // 默认调用者
        $defaultCaller = new AccessKey();
        $defaultCaller->setTitle('默认调用者');
        $defaultCaller->setAppId('default-' . bin2hex(random_bytes(8)));
        $defaultCaller->setAppSecret(bin2hex(random_bytes(16)));
        $defaultCaller->setSignTimeoutSecond(180);
        $defaultCaller->setValid(true);
        $defaultCaller->setRemark('基本配置的JSON-RPC调用方');
        $manager->persist($defaultCaller);
        $this->addReference(self::DEFAULT_CALLER_REFERENCE, $defaultCaller);

        // 自定义超时调用者
        $longTimeoutCaller = new AccessKey();
        $longTimeoutCaller->setTitle('长超时调用者');
        $longTimeoutCaller->setAppId('long-timeout-' . bin2hex(random_bytes(8)));
        $longTimeoutCaller->setAppSecret(bin2hex(random_bytes(16)));
        $longTimeoutCaller->setSignTimeoutSecond(3600); // 1小时超时
        $longTimeoutCaller->setValid(true);
        $longTimeoutCaller->setRemark('配置了较长签名超时时间的调用方');
        $manager->persist($longTimeoutCaller);
        $this->addReference(self::LONG_TIMEOUT_CALLER_REFERENCE, $longTimeoutCaller);

        // IP白名单调用者
        $ipRestrictedCaller = new AccessKey();
        $ipRestrictedCaller->setTitle('IP限制调用者');
        $ipRestrictedCaller->setAppId('ip-restricted-' . bin2hex(random_bytes(8)));
        $ipRestrictedCaller->setAppSecret(bin2hex(random_bytes(16)));
        $ipRestrictedCaller->setSignTimeoutSecond(180);
        $ipRestrictedCaller->setAllowIps(['127.0.0.1', '192.168.1.100', '10.0.0.1']);
        $ipRestrictedCaller->setValid(true);
        $ipRestrictedCaller->setRemark('配置了IP白名单的调用方');
        $manager->persist($ipRestrictedCaller);
        $this->addReference(self::IP_RESTRICTED_CALLER_REFERENCE, $ipRestrictedCaller);

        // 带AES密钥调用者
        $aesEnabledCaller = new AccessKey();
        $aesEnabledCaller->setTitle('AES加密调用者');
        $aesEnabledCaller->setAppId('aes-enabled-' . bin2hex(random_bytes(8)));
        $aesEnabledCaller->setAppSecret(bin2hex(random_bytes(16)));
        $aesEnabledCaller->setSignTimeoutSecond(180);
        $aesEnabledCaller->setAesKey(bin2hex(random_bytes(16)));
        $aesEnabledCaller->setValid(true);
        $aesEnabledCaller->setRemark('配置了AES加密密钥的调用方');
        $manager->persist($aesEnabledCaller);
        $this->addReference(self::AES_ENABLED_CALLER_REFERENCE, $aesEnabledCaller);

        // 无效调用者
        $invalidCaller = new AccessKey();
        $invalidCaller->setTitle('无效调用者');
        $invalidCaller->setAppId('invalid-' . bin2hex(random_bytes(8)));
        $invalidCaller->setAppSecret(bin2hex(random_bytes(16)));
        $invalidCaller->setSignTimeoutSecond(180);
        $invalidCaller->setValid(false);
        $invalidCaller->setRemark('标记为无效状态的调用方');
        $manager->persist($invalidCaller);
        $this->addReference(self::INVALID_CALLER_REFERENCE, $invalidCaller);

        $manager->flush();
    }
}
