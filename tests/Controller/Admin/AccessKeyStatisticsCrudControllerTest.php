<?php

declare(strict_types=1);

namespace Tourze\AccessKeyBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\AccessKeyBundle\Controller\Admin\AccessKeyStatisticsCrudController;
use Tourze\AccessKeyBundle\Entity\AccessKeyStatistics;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * AccessKeyStatisticsCrudController 测试
 *
 * 注意：本控制器禁用了 NEW 和 DELETE 操作，因此相关的测试会因权限异常失败。
 * 这是预期行为，表示操作禁用生效。
 *
 * 跳过的测试说明（预期行为）：
 * - testNewPageFieldsProviderHasData: 自动跳过，因为NEW操作被禁用（见testNewActionIsDisabled验证）
 *
 * @internal
 */
#[CoversClass(AccessKeyStatisticsCrudController::class)]
#[RunTestsInSeparateProcesses]
final class AccessKeyStatisticsCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * 获取控制器服务实例
     */
    protected function getControllerService(): AccessKeyStatisticsCrudController
    {
        return self::getService(AccessKeyStatisticsCrudController::class);
    }

    /**
     * 提供索引页表头数据
     *
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield 'AccessKey' => ['AccessKey'];
        yield '统计小时' => ['统计小时'];
        yield '成功次数' => ['成功次数'];
        yield '失败次数' => ['失败次数'];
        yield '总次数' => ['总次数'];
        yield '成功率' => ['成功率'];
    }

    /**
     * 提供编辑页字段数据
     *
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'accessKey' => ['accessKey'];
        yield 'hour' => ['hour'];
        yield 'successCount' => ['successCount'];
        yield 'failureCount' => ['failureCount'];
    }

    /**
     * 提供新增页字段数据
     * NEW action被控制器禁用，提供最小字段数据以满足测试框架要求
     * 注意：相关的字段测试会因权限异常而失败，这是预期行为
     *
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        // 为了满足测试框架的要求，提供一个最小的字段数据
        // NEW 操作被禁用，相关测试会失败，这是预期的
        yield 'accessKey' => ['accessKey'];
    }

    public function testEntityFqcn(): void
    {
        // 验证实体类名获取
        $this->assertSame(AccessKeyStatistics::class, AccessKeyStatisticsCrudController::getEntityFqcn());
    }

    public function testIndexPage(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/admin?entityName=AccessKeyStatistics&action=index');

        // 使用客户端特定的响应断言
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testEditPageAccessible(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 访问编辑页面
        $crawler = $client->request('GET', '/admin?entityName=AccessKeyStatistics&action=edit&entityId=1');

        // 使用客户端特定的响应断言
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testDetailPageAccessible(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 访问详情页面
        $crawler = $client->request('GET', '/admin?entityName=AccessKeyStatistics&action=detail&entityId=1');

        // 使用客户端特定的响应断言
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testNewActionIsDisabled(): void
    {
        // 验证NEW操作确实被控制器禁用
        $actions = Actions::new();
        $configuredActions = $this->getControllerService()->configureActions($actions);

        // 检查所有页面的禁用操作
        $allPages = [Crud::PAGE_INDEX, Crud::PAGE_DETAIL];
        $isNewDisabled = false;

        foreach ($allPages as $pageName) {
            try {
                $pageActions = $configuredActions->getAsDto($pageName);
                if (in_array(Action::NEW, $pageActions->getDisabledActions(), true)) {
                    $isNewDisabled = true;
                    break;
                }
            } catch (\Exception) {
                // 某些页面可能不存在，继续检查
                continue;
            }
        }

        $this->assertTrue($isNewDisabled, 'NEW操作应该被AccessKeyStatisticsCrudController禁用');
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();

        // 直接通过Symfony验证器测试实体验证规则
        // 这个测试验证必填字段的验证，等同于表单提交空表单时的验证
        $accessKeyStatistics = new AccessKeyStatistics();

        /** @var ValidatorInterface $validator */
        $validator = self::getContainer()->get('validator');
        $violations = $validator->validate($accessKeyStatistics);

        // 验证必填字段的错误
        $this->assertGreaterThan(0, count($violations), '空的AccessKeyStatistics实体应该有验证错误');

        $violationMessages = [];
        foreach ($violations as $violation) {
            $violationMessages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
        }

        // 验证必填字段（accessKey为NotNull, hour为NotBlank）都有相应的验证错误
        $requiredFields = ['accessKey', 'hour'];
        foreach ($requiredFields as $field) {
            $hasFieldViolation = false;
            foreach ($violations as $violation) {
                if ($violation->getPropertyPath() === $field) {
                    $hasFieldViolation = true;
                    // 验证错误信息包含"should not be blank"或"should not be null"
                    $this->assertTrue(
                        str_contains((string) $violation->getMessage(), 'should not be blank')
                        || str_contains((string) $violation->getMessage(), 'should not be null'),
                        sprintf('字段 "%s" 的错误信息应包含验证失败的消息: %s', $field, $violation->getMessage())
                    );
                    break;
                }
            }
            $this->assertTrue($hasFieldViolation, sprintf('字段 "%s" 应该有验证错误', $field));
        }
    }
}
