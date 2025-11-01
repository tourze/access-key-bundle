<?php

namespace Tourze\AccessKeyBundle\Tests\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\AccessKeyBundle\Controller\Admin\AccessKeyCrudController;
use Tourze\AccessKeyBundle\Entity\AccessKey;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * ApiCallerCrudController 测试.
 *
 * @internal
 */
#[CoversClass(AccessKeyCrudController::class)]
#[RunTestsInSeparateProcesses]
final class AccessKeyCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * 提供索引页表头数据.
     *
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];

        yield '名称' => ['名称'];

        yield 'AppID' => ['AppID'];

        yield 'AppSecret' => ['AppSecret'];

        yield '允许调用IP' => ['允许调用IP'];

        yield '签名超时时间' => ['签名超时时间'];

        yield '有效状态' => ['有效状态'];

        yield '创建人' => ['创建人'];

        yield '更新人' => ['更新人'];

        yield '创建时间' => ['创建时间'];

        yield '更新时间' => ['更新时间'];
    }

    /**
     * 提供编辑页字段数据.
     *
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'title' => ['title'];

        yield 'appId' => ['appId'];

        yield 'appSecret' => ['appSecret'];

        // allowIps (ArrayField) 单独测试，见 testAllowIpsArrayFieldExistsOnEditPage
        yield 'signTimeoutSecond' => ['signTimeoutSecond'];

        yield 'aesKey' => ['aesKey'];

        yield 'remark' => ['remark'];

        yield 'valid' => ['valid'];
    }

    /**
     * 提供新增页字段数据.
     *
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'title' => ['title'];

        yield 'appId' => ['appId'];

        yield 'appSecret' => ['appSecret'];

        // allowIps (ArrayField) 单独测试，见 testAllowIpsArrayFieldExists
        yield 'signTimeoutSecond' => ['signTimeoutSecond'];

        yield 'aesKey' => ['aesKey'];

        yield 'remark' => ['remark'];

        yield 'valid' => ['valid'];
    }

    public function testEntityFqcn(): void
    {
        // 验证实体类名获取
        $this->assertSame(AccessKey::class, AccessKeyCrudController::getEntityFqcn());
    }

    public function testNewPageAccessible(): void
    {
        $client = self::createAuthenticatedClient();

        // 访问新建页面
        $crawler = $client->request('GET', '/admin?entityName=AccessKey&action=new');

        // 使用客户端特定的响应断言
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertNotNull($crawler, '页面应该成功渲染');
    }

    public function testIndexPage(): void
    {
        $client = self::createAuthenticatedClient();

        $crawler = $client->request('GET', '/admin?entityName=AccessKey&action=index');

        // 使用客户端特定的响应断言
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertNotNull($crawler, '索引页面应该成功渲染');
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();

        // 直接通过Symfony验证器测试实体验证规则
        // 这个测试验证必填字段的验证，等同于表单提交空表单时的验证
        $accessKey = new AccessKey();

        /** @var ValidatorInterface $validator */
        $validator = self::getContainer()->get('validator');
        $violations = $validator->validate($accessKey);

        // 验证必填字段的错误
        $this->assertGreaterThan(0, count($violations), '空的AccessKey实体应该有验证错误');

        $violationMessages = [];
        foreach ($violations as $violation) {
            $violationMessages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
        }

        // 验证必填字段（title, appId）都有相应的验证错误
        $expectedFields = ['title', 'appId'];
        foreach ($expectedFields as $field) {
            $hasFieldViolation = false;
            foreach ($violations as $violation) {
                if ($violation->getPropertyPath() === $field) {
                    $hasFieldViolation = true;
                    // 验证错误信息包含"should not be blank"
                    $this->assertStringContainsString('should not be blank', $violation->getMessage());

                    break;
                }
            }
            $this->assertTrue($hasFieldViolation, sprintf('字段 "%s" 应该有验证错误', $field));
        }
    }

    /**
     * 专门测试 ArrayField (allowIps) 在新增页的存在性.
     */
    public function testAllowIpsArrayFieldExists(): void
    {
        $client = $this->createAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrl(Action::NEW));

        $this->assertResponseIsSuccessful();
        $this->assertAllowIpsFieldInHtml($crawler);
        $this->assertTrue(true, 'allowIps 字段存在性检查完成');
    }

    /**
     * 专门测试 ArrayField (allowIps) 在编辑页的存在性.
     */
    public function testAllowIpsArrayFieldExistsOnEditPage(): void
    {
        $client = $this->createAuthenticatedClient();
        $accessKey = $this->createTestEntity();
        $crawler = $client->request('GET', $this->generateAdminUrl(Action::EDIT, ['entityId' => $accessKey->getId()]));

        $this->assertResponseIsSuccessful();
        $this->assertAllowIpsFieldInHtml($crawler);
        $this->assertTrue(true, 'allowIps 字段在编辑页存在性检查完成');
    }

    /**
     * 获取控制器服务实例.
     */
    protected function getControllerService(): AccessKeyCrudController
    {
        return self::getService(AccessKeyCrudController::class);
    }

    /**
     * 创建测试实体.
     */
    private function createTestEntity(): AccessKey
    {
        $accessKey = new AccessKey();
        $accessKey->setTitle('Test Access Key');
        $accessKey->setAppId('test_app_id');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->persist($accessKey);
        $entityManager->flush();

        return $accessKey;
    }

    /**
     * 验证 allowIps 字段在页面 HTML 中的存在性.
     */
    private function assertAllowIpsFieldInHtml(Crawler $crawler): void
    {
        $html = $crawler->html();

        // 查找标签或配置
        if (str_contains($html, '允许调用IP') || str_contains($html, 'allowIps')) {
            $this->assertTrue(true, 'allowIps 字段已正确配置到表单中');

            return;
        }

        $this->checkFieldSelectorsInCrawler($crawler);
    }

    /**
     * 检查各种可能的字段选择器.
     */
    private function checkFieldSelectorsInCrawler(Crawler $crawler): void
    {
        $selectors = $this->getAllowIpsFieldSelectors();
        $foundSelectors = $this->findMatchingSelectors($crawler, $selectors);

        if ([] === $foundSelectors) {
            $this->failWithFieldDebugInfo($crawler, $selectors);
        }

        $this->assertTrue(true, sprintf('allowIps 字段找到了。匹配的选择器: %s', implode(', ', $foundSelectors)));
    }

    /**
     * 获取所有可能的 allowIps 字段选择器.
     *
     * @return string[]
     */
    private function getAllowIpsFieldSelectors(): array
    {
        return [
            'input[name*="allowIps"]',
            'textarea[name*="allowIps"]',
            'select[name*="allowIps"]',
            '.field-allowips',
            '.field-allow-ips',
            '[data-field="allowIps"]',
            '[data-field-name="allowIps"]',
            '.ea-array-field',
            '.collection-widget',
            '[name*="allowIps"], [id*="allowIps"], [class*="allowIps"], [data-*="allowIps"]',
        ];
    }

    /**
     * 查找匹配的选择器.
     *
     * @param string[] $selectors
     *
     * @return string[]
     */
    private function findMatchingSelectors(Crawler $crawler, array $selectors): array
    {
        $foundSelectors = [];

        foreach ($selectors as $selector) {
            $count = $crawler->filter($selector)->count();
            if ($count > 0) {
                $foundSelectors[] = "{$selector} ({$count})";
            }
        }

        return $foundSelectors;
    }

    /**
     * 当字段未找到时输出调试信息并失败.
     *
     * @param string[] $selectors
     */
    private function failWithFieldDebugInfo(Crawler $crawler, array $selectors): void
    {
        $formFields = $crawler->filter('form input, form select, form textarea, form [data-field]');
        $fieldNames = [];

        $formFields->each(function (Crawler $element) use (&$fieldNames): void {
            $name = $element->attr('name') ?? $element->attr('data-field') ?? 'unknown';
            $fieldNames[] = $name;
        });

        self::fail(sprintf(
            "allowIps 字段未找到。\n表单中的字段: %s\n已检查的选择器: %s",
            implode(', ', $fieldNames),
            implode(', ', $selectors)
        ));
    }
}
