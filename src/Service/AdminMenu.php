<?php

namespace Tourze\AccessKeyBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\AccessKeyBundle\Entity\AccessKey;
use Tourze\AccessKeyBundle\Entity\AccessKeyStatistics;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

/**
 * JSON-RPC调用者管理菜单服务
 */
#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('接口管理')) {
            $item->addChild('接口管理');
        }

        $apiMenu = $item->getChild('接口管理');

        if (null === $apiMenu) {
            return;
        }

        // API调用者管理菜单
        $apiMenu->addChild('调用者管理')
            ->setUri($this->linkGenerator->getCurdListPage(AccessKey::class))
            ->setAttribute('icon', 'fas fa-key')
        ;

        // API调用统计菜单
        $apiMenu->addChild('访问统计')
            ->setUri($this->linkGenerator->getCurdListPage(AccessKeyStatistics::class))
            ->setAttribute('icon', 'fas fa-chart-bar')
        ;
    }
}
