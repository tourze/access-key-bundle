<?php

namespace Tourze\AccessKeyBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\AccessKeyBundle\Entity\AccessKey;
use Tourze\AccessKeyBundle\Entity\AccessKeyStatistics;
use Tourze\AccessKeyBundle\Service\AdminMenu;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private AdminMenu $adminMenu;

    private LinkGeneratorInterface $linkGenerator;

    protected function onSetUp(): void
    {
        // 创建测试专用的LinkGenerator实现
        $this->linkGenerator = new class implements LinkGeneratorInterface {
            public function getCurdListPage(string $entityClass): string
            {
                return match ($entityClass) {
                    AccessKey::class => '/admin/api-caller',
                    AccessKeyStatistics::class => '/admin/statistics',
                    default => '/admin/unknown',
                };
            }

            public function extractEntityFqcn(string $url): ?string
            {
                return match (true) {
                    str_contains($url, '/admin/api-caller') => AccessKey::class,
                    str_contains($url, '/admin/statistics') => AccessKeyStatistics::class,
                    default => null,
                };
            }

            public function setDashboard(string $dashboardControllerFqcn): void
            {
                // 测试中不需要实际实现
            }
        };

        self::getContainer()->set(LinkGeneratorInterface::class, $this->linkGenerator);
        $this->adminMenu = self::getService(AdminMenu::class);
    }

    protected function getMenuProvider(): object
    {
        return $this->adminMenu;
    }

    public function testInvokeAddsApiCallerMenu(): void
    {
        $this->assertInstanceOf(AdminMenu::class, $this->adminMenu);

        // 创建真实的菜单项
        $factory = new MenuFactory();
        $mainItem = new MenuItem('root', $factory);

        // 执行菜单构建
        $this->adminMenu->__invoke($mainItem);

        // 验证接口管理菜单被创建
        $apiMenu = $mainItem->getChild('接口管理');
        $this->assertInstanceOf(ItemInterface::class, $apiMenu);

        // 验证调用者管理子菜单
        $accessKeyMenuItem = $apiMenu->getChild('调用者管理');
        $this->assertInstanceOf(ItemInterface::class, $accessKeyMenuItem);
        $this->assertSame('/admin/api-caller', $accessKeyMenuItem->getUri());
        $this->assertSame('fas fa-key', $accessKeyMenuItem->getAttribute('icon'));

        // 验证访问统计子菜单
        $statisticsMenuItem = $apiMenu->getChild('访问统计');
        $this->assertInstanceOf(ItemInterface::class, $statisticsMenuItem);
        $this->assertSame('/admin/statistics', $statisticsMenuItem->getUri());
        $this->assertSame('fas fa-chart-bar', $statisticsMenuItem->getAttribute('icon'));
    }

    public function testInvokeWithExistingApiMenu(): void
    {
        $this->assertInstanceOf(AdminMenu::class, $this->adminMenu);

        // 创建已有接口管理菜单的主菜单
        $factory = new MenuFactory();
        $mainItem = new MenuItem('root', $factory);
        $existingApiMenu = $mainItem->addChild('接口管理');

        // 执行菜单构建
        $this->adminMenu->__invoke($mainItem);

        // 验证使用了现有的接口管理菜单
        $apiMenu = $mainItem->getChild('接口管理');
        $this->assertSame($existingApiMenu, $apiMenu);

        // 验证子菜单被正确添加
        $accessKeyMenuItem = $apiMenu->getChild('调用者管理');
        $this->assertInstanceOf(ItemInterface::class, $accessKeyMenuItem);
        $this->assertSame('/admin/api-caller', $accessKeyMenuItem->getUri());
        $this->assertSame('fas fa-key', $accessKeyMenuItem->getAttribute('icon'));

        $statisticsMenuItem = $apiMenu->getChild('访问统计');
        $this->assertInstanceOf(ItemInterface::class, $statisticsMenuItem);
        $this->assertSame('/admin/statistics', $statisticsMenuItem->getUri());
        $this->assertSame('fas fa-chart-bar', $statisticsMenuItem->getAttribute('icon'));
    }
}
