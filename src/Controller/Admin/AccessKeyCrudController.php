<?php

namespace Tourze\AccessKeyBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\AccessKeyBundle\Entity\AccessKey;

/**
 * API调用者管理控制器
 *
 * @extends AbstractCrudController<AccessKey>
 */
#[AdminCrud(routePath: '/access-key/access-key', routeName: 'access_key_access_key')]
final class AccessKeyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AccessKey::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('API调用者')
            ->setEntityLabelInPlural('API调用者')
            ->setPageTitle('index', 'API调用者列表')
            ->setPageTitle('new', '新建API调用者')
            ->setPageTitle('edit', '编辑API调用者')
            ->setPageTitle('detail', 'API调用者详情')
            ->setHelp('index', '管理系统中的API调用者配置，包括签名验证、IP白名单等安全设置')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'title', 'appId'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
        ;

        yield TextField::new('title', '名称')
            ->setRequired(true)
            ->setHelp('API调用者的显示名称')
        ;

        yield TextField::new('appId', 'AppID')
            ->setRequired(true)
            ->setHelp('API调用者的唯一标识符')
        ;

        yield TextField::new('appSecret', 'AppSecret')
            ->setRequired(false)
            ->setHelp('用于签名验证的密钥')
        ;

        yield ArrayField::new('allowIps', '允许调用IP')
            ->setHelp('IP白名单，支持CIDR格式，为空表示不限制')
        ;

        yield IntegerField::new('signTimeoutSecond', '签名超时时间')
            ->setHelp('签名有效期，单位：秒')
            ->setFormTypeOption('attr', ['min' => 1, 'max' => 3600])
        ;

        yield TextareaField::new('aesKey', 'AES Key')
            ->hideOnIndex()
            ->setHelp('用于数据加密的AES密钥')
        ;

        yield TextareaField::new('remark', '备注')
            ->hideOnIndex()
            ->setRequired(false)
        ;

        yield BooleanField::new('valid', '有效状态')
            ->setHelp('是否启用该API调用者')
        ;

        yield TextField::new('createdBy', '创建人')
            ->hideOnForm()
        ;

        yield TextField::new('updatedBy', '更新人')
            ->hideOnForm()
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('title', '名称'))
            ->add(TextFilter::new('appId', 'AppID'))
            ->add(BooleanFilter::new('valid', '有效状态'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
        ;
    }
}
