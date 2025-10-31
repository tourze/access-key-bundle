<?php

namespace Tourze\AccessKeyBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\AccessKeyBundle\Exception\InvalidRepositoryException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(InvalidRepositoryException::class)]
final class InvalidRepositoryExceptionTest extends AbstractExceptionTestCase
{
    public function testConstructorFormatsMessageCorrectly(): void
    {
        $expectedClass = 'App\Repository\ExpectedRepository';
        $actualClass = 'App\Repository\ActualRepository';

        $exception = new InvalidRepositoryException($expectedClass, $actualClass);

        $expectedMessage = sprintf(
            'Expected repository of type %s, but got %s',
            $expectedClass,
            $actualClass
        );

        self::assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionExtendsRuntimeException(): void
    {
        $exception = new InvalidRepositoryException('Expected', 'Actual');

        // 验证异常类继承关系
        $reflection = new \ReflectionClass($exception);
        self::assertTrue($reflection->isSubclassOf(\RuntimeException::class));

        // 验证异常可以被正确抛出和捕获
        try {
            throw $exception;
        } catch (InvalidRepositoryException $e) {
            self::assertSame($exception, $e);
        }
    }
}
