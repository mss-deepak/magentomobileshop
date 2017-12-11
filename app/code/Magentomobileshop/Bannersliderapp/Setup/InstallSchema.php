<?php
namespace Magentomobileshop\Bannersliderapp\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magentomobile_bannersliderapp')
        )->addColumn(
            'banner_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Banner Id'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Banner Name'
        )->addColumn(
            'order_banner',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            '11',
            ['nullable' => false],
            'Order Banner'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            6,
            ['nullable' => false],
            'Status'
        )->addColumn(
            'url_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Url Type'
        )->addColumn(
            'check_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Check Type'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            [],
            'Product Id'
        )->addColumn(
            'category_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            [],
            'Category Id'
        )->addColumn(
            'thumbnail',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'image'
        )->addColumn(
            'image_alt',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Image Alt'
        )->setComment(
            'Row Data Table'
        );
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
