<?php


namespace Snowdog\ShippingLatency\Setup;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * UpgradeData constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->addShippingLatencyAttribute($setup);
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->addDisplayInStockFrontendAttribute($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param EavSetup $eavSetup
     * @param string $attributeCode
     */
    protected function updateShippingLatencyAttribute(EavSetup $eavSetup, string $attributeCode)
    {
        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode,
            [
                'group' => 'General',
                'type' => 'varchar',
                'label' => 'Shipping Latency',
                'input' => 'select',
                'source' => 'Snowdog\ShippingLatency\Model\Config\Source\Options',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '0',
                'visible_on_front' => true,
                'used_in_product_listing' => true,
            ]
        );
    }

    private function addShippingLatencyAttribute(ModuleDataSetupInterface $setup)
    {
        $attributeCode = 'shipping_latency';
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $attributeBackendType = $eavSetup->getAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode,
            'backend_type'
        );

        if (isset($attributeBackendType) && $attributeBackendType === 'varchar') {
            return;
        }

        $this->updateShippingLatencyAttribute($eavSetup, $attributeCode);
    }

    private function addDisplayInStockFrontendAttribute(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'display_instock_frontend',
            [
                'group' => 'General',
                'type' => 'varchar',
                'label' => 'Display In Stock In Frontend',
                'input' => 'select',
                'source' => Boolean::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '1',
                'visible_on_front' => true,
                'used_in_product_listing' => true,
            ]
        );
    }
}
