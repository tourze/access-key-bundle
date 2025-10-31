<?php

namespace Tourze\AccessKeyBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\AccessKeyBundle\DependencyInjection\AccessKeyExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(AccessKeyExtension::class)]
final class AccessKeyExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private AccessKeyExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new AccessKeyExtension();
    }

    public function testGetConfigDir(): void
    {
        $reflection = new \ReflectionClass($this->extension);
        $method = $reflection->getMethod('getConfigDir');
        $method->setAccessible(true);

        $configDir = $method->invoke($this->extension);

        $this->assertIsString($configDir);
        $this->assertStringEndsWith('/Resources/config', $configDir);
    }

    public function testGetAlias(): void
    {
        $alias = $this->extension->getAlias();

        $this->assertEquals('access_key', $alias);
    }
}
