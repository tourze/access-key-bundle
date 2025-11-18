<?php

namespace Tourze\AccessKeyBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AccessKeyBundle\AccessKeyBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(AccessKeyBundle::class)]
#[RunTestsInSeparateProcesses]
final class AccessKeyBundleTest extends AbstractBundleTestCase
{
}
