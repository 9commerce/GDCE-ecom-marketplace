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
 * @package     Ced_CsProduct
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProduct\Ui\DataProvider;

class Attributes extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $manager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler
     */
    protected $configurableAttributeHandler;

    /**
     * Attributes constructor.
     * @param \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $configurableAttributeHandler
     * @param \Magento\Framework\App\Request\Http $http
     * @param \Magento\Framework\Module\Manager $manager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $configurableAttributeHandler,
        \Magento\Framework\App\Request\Http $http,
        \Magento\Framework\Module\Manager $manager,
        \Magento\Customer\Model\Session $customerSession,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $http;
        $this->manager = $manager;
        $this->customerSession = $customerSession;
        $this->configurableAttributeHandler = $configurableAttributeHandler;
        $this->collection = $configurableAttributeHandler->getApplicableAttributes()
            ->setAttributeSetFilter($this->request->getParam('set'));
        $this->prepareUpdateUrl();
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $is_vpattribute = $this->manager->isEnabled('Ced_CsVendorProductAttribute');
        $otherVendorAttributeIds = [];
        if ($is_vpattribute) {
            $vendorId = $this->customerSession->getVendorId();
            $ob = \Magento\Framework\App\ObjectManager::getInstance();
            $otherVendorAttributeIds = $ob->create(\Ced\CsVendorProductAttribute\Model\Attribute::class)
                ->getCollection()
                ->addFieldtoFilter('vendor_id', ['neq' => $vendorId])->getColumnValues('attribute_id');
        }
        $items = [];
        $skippedItems = 0;
        foreach ($this->getCollection()->getItems() as $attribute) {
            if (in_array($attribute->getId(), $otherVendorAttributeIds)) {
                $skippedItems++;
                continue;
            }
            if ($this->configurableAttributeHandler->isAttributeApplicable($attribute)) {
                $items[] = $attribute->toArray();

            } else {
                $skippedItems++;

            }
        }

        return [
            'totalRecords' => $this->collection->getSize() - $skippedItems,
            'items' => $items
        ];
    }

    /**
     * @return void
     */
    protected function prepareUpdateUrl()
    {
        if (!isset($this->data['config']['filter_url_params'])) {
            return;
        }
        foreach ($this->data['config']['filter_url_params'] as $paramName => $paramValue) {

            if ('*' == $paramValue) {
                $paramValue = $this->request->getParam($paramName);
            }

            if ($paramValue) {
                $this->data['config']['update_url'] = sprintf(
                    '%s%s/%s/',
                    $this->data['config']['update_url'],
                    $paramName,
                    $paramValue
                );
            }
        }
    }
}
