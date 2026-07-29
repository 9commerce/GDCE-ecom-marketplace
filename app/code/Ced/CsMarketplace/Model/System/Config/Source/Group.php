<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Model\System\Config\Source;

/**
 * Class Group
 * @package Ced\CsMarketplace\Model\System\Config\Source
 */
class Group extends \Ced\CsMarketplace\Model\System\Config\Source\AbstractBlock
{

    const XML_PATH_CED_CSMARKETPLACE_VENDOR_GROUPS = 'ced_csmarketplace/vendor/groups';

    public static $GROUPS_ARRAY = [];

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $_context;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_cedeventManager;

    /**
     * Group constructor.
     * @param \Magento\Framework\Event\ManagerInterface $cedeventManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $cedeventManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
    ) {

        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
        $this->_scopeConfig = $scopeConfig;
        $this->_cedeventManager = $cedeventManager;

    }

    /**
     * @return array
     */
    public function getVendorGroups()
    {
        $groups = array_keys($this->getGroups());
        $options = [];
        foreach ($groups as $group) {
            $group = strtolower(trim($group));

            $options[] = ['value' => $group, 'label' => __(ucfirst($group))];
        }

        return $options;
    }

    /**
     * Retrieve groups data form config.xml
     *
     * @return array
     */
    public function getGroups()
    {
        $groups = $this->_scopeConfig->getValue(self::XML_PATH_CED_CSMARKETPLACE_VENDOR_GROUPS);
        self::$GROUPS_ARRAY = json_decode(json_encode($groups), true);

        $this->_cedeventManager->dispatch(
            'ced_csmarketplace_vendor_group_prepare',
            ['class' => 'Ced\CsMarketplace\Model\System\Config\Source\Group']
        );
        return self::$GROUPS_ARRAY;
    }

    /**
     * @param bool $defaultValues
     * @param bool $withEmpty
     * @param null $storeId
     * @return array|String
     */
    public function toFilterOptionArray($defaultValues = false, $withEmpty = false, $storeId = null)
    {
        if ($storeId == null)
            $options = $this->toOptionArray();
        else
            $options = $this->toOptionArray();
        $filterOptions = [];

        if (count($options)) {
            foreach ($options as $option) {
                if (isset($option['value']) && isset($option['label'])) {
                    $filterOptions[$option['value']] = $option['label'];
                }
            }
        }
        return $filterOptions;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        if (is_array($this->getGroups())) {
            $groups = array_keys($this->getGroups());

            foreach ($groups as $group) {
                $group = strtolower(trim($group));
                $options[] = ['value' => $group, 'label' => __(ucfirst($group))];
            }
        }
        return $options;
    }
}
