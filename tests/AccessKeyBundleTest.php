<?php

namespace Tourze\AccessKeyBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AccessKeyBundle\AccessKeyBundle;
use Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle;
use Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle;
use Tourze\DoctrineTrackBundle\DoctrineTrackBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(AccessKeyBundle::class)]
#[RunTestsInSeparateProcesses]
final class AccessKeyBundleTest extends AbstractBundleTestCase
{
    public function testGetBundleDependencies(): void
    {
        $dependencies = AccessKeyBundle::getBundleDependencies();

        $this->assertIsArray($dependencies);
        $this->assertArrayHasKey(DoctrineBundle::class, $dependencies);
        $this->assertArrayHasKey(DoctrineSnowflakeBundle::class, $dependencies);
        $this->assertArrayHasKey(DoctrineTimestampBundle::class, $dependencies);
        $this->assertArrayHasKey(DoctrineTrackBundle::class, $dependencies);

        $this->assertEquals(['all' => true], $dependencies[DoctrineBundle::class]);
        $this->assertEquals(['all' => true], $dependencies[DoctrineSnowflakeBundle::class]);
        $this->assertEquals(['all' => true], $dependencies[DoctrineTimestampBundle::class]);
        $this->assertEquals(['all' => true], $dependencies[DoctrineTrackBundle::class]);
    }
}
