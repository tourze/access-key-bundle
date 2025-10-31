<?php

declare(strict_types=1);

namespace Tourze\AccessKeyBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use Tourze\AccessKeyBundle\Entity\AccessKey;
use Tourze\AccessKeyBundle\Entity\AccessKeyStatistics;

/**
 * AccessKey统计管理控制器
 *
 * @extends AbstractCrudController<AccessKeyStatistics>
 */
#[AdminCrud(routePath: '/access-key/statistics', routeName: 'access_key_statistics')]
final class AccessKeyStatisticsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AccessKeyStatistics::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('访问统计')
            ->setEntityLabelInPlural('访问统计')
            ->setPageTitle('index', 'AccessKey访问统计')
            ->setPageTitle('new', '新建访问统计')
            ->setPageTitle('edit', '编辑访问统计')
            ->setPageTitle('detail', '访问统计详情')
            ->setHelp('index', '按小时统计AccessKey的调用成功和失败次数')
            ->setDefaultSort(['hour' => 'DESC', 'id' => 'DESC'])
            ->setSearchFields(['accessKey.title', 'accessKey.appId'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
        ;

        yield AssociationField::new('accessKey', 'AccessKey')
            ->setRequired(true)
            ->setHelp('关联的API调用者')
            ->autocomplete()
        ;

        yield DateTimeField::new('hour', '统计小时')
            ->setRequired(true)
            ->setHelp('统计的小时时间点（精确到小时）')
            ->setFormat('yyyy-MM-dd HH:00')
        ;

        yield IntegerField::new('successCount', '成功次数')
            ->setRequired(true)
            ->setHelp('该小时内成功调用的次数')
            ->setFormTypeOption('attr', ['min' => 0])
        ;

        yield IntegerField::new('failureCount', '失败次数')
            ->setRequired(true)
            ->setHelp('该小时内失败调用的次数')
            ->setFormTypeOption('attr', ['min' => 0])
        ;

        yield IntegerField::new('totalCount', '总次数')
            ->hideOnForm()
            ->setHelp('总调用次数（成功+失败）')
        ;

        yield NumberField::new('successRate', '成功率')
            ->hideOnForm()
            ->setHelp('成功调用的比例')
            ->setNumDecimals(2)
            ->formatValue(function ($value) {
                assert(is_float($value) || is_int($value));
                return ($value * 100) . '%';
            })
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::DELETE)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('accessKey', 'AccessKey'))
            ->add(DateTimeFilter::new('hour', '统计小时'))
            ->add(NumericFilter::new('successCount', '成功次数'))
            ->add(NumericFilter::new('failureCount', '失败次数'))
        ;
    }
}
