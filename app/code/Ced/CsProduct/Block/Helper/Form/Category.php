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

namespace Ced\CsProduct\Block\Helper\Form;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\AuthorizationInterface;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Category as FormCategory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\CollectionFactory as ElementCollectionFactory;
use Magento\Framework\Escaper;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Backend\Helper\Data;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Json\EncoderInterface;

class Category extends FormCategory
{

    /**
     * @var LayoutInterface
     */
    protected $_layout;

    /**
     * Backend data
     *
     * @var Data
     */
    protected $_backendData;

    /**
     * @var CategoryCollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * Category constructor.
     * @param Factory $factoryElement
     * @param ElementCollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param CategoryCollectionFactory $collectionFactory
     * @param Data $backendData
     * @param LayoutInterface $layout
     * @param EncoderInterface $jsonEncoder
     * @param AuthorizationInterface $authorization
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        ElementCollectionFactory $factoryCollection,
        Escaper $escaper,
        CategoryCollectionFactory $collectionFactory,
        Data $backendData,
        LayoutInterface $layout,
        EncoderInterface $jsonEncoder,
        AuthorizationInterface $authorization,
        array $data = []
    ) {
        parent::__construct(
            $factoryElement,
            $factoryCollection,
            $escaper,
            $collectionFactory,
            $backendData,
            $layout,
            $jsonEncoder,
            $authorization,
            $data
        );
        $this->_jsonEncoder = $jsonEncoder;
        $this->_collectionFactory = $collectionFactory;
        $this->_backendData = $backendData;
        $this->authorization = $authorization;
        $this->_layout = $layout;
        if (!$this->isAllowed()) {
            $this->setType('hidden');
            $this->addClass('hidden');
        }
    }

    /**
     * Get values for select
     *
     * @return array
     */
    public function getValues()
    {
        $collection = $this->_getCategoriesCollection();
        $values = $this->getValue();
        if (!is_array($values)) {
            $values = explode(',', $values);
        }
        $collection->addAttributeToSelect('name');
        $collection->addIdFilter($values);

        $options = [];

        foreach ($collection as $category) {
            $options[] = ['label' => $category->getName(), 'value' => $category->getId()];
        }
        return $options;
    }

    /**
     * Get categories collection
     *
     * @return Collection
     */
    protected function _getCategoriesCollection()
    {
        return $this->_collectionFactory->create();
    }

    /**
     * Attach category suggest widget initialization
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        if (!$this->isAllowed()) {
            return '';
        }
        $htmlId = $this->getHtmlId();
        $suggestPlaceholder = __('start typing to search category');
        $selectorOptions = $this->_jsonEncoder->encode($this->_getSelectorOptions());
        $newCategoryCaption = __('New Category');

        $button = $this->_layout->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'add_category_button',
                'label' => $newCategoryCaption,
                'title' => $newCategoryCaption,
                'onclick' => 'jQuery("#new-category").modal("openModal")',
                'disabled' => $this->getDisabled(),
            ]
        );
        $return = <<<HTML
    <input id="{$htmlId}-suggest" placeholder="$suggestPlaceholder" />
    <script>
        require(["jquery", "mage/mage"], function($){
            $('#{$htmlId}-suggest').mage('treeSuggest', {$selectorOptions});
        });
    </script>
HTML;
        return $return . $button->toHtml();
    }

    /**
     * Get selector options
     *
     * @return array
     */
    protected function _getSelectorOptions()
    {
        return [
            'source' => $this->_backendData->getUrl('catalog/category/suggestCategories'),
            'valueField' => '#' . $this->getHtmlId(),
            'className' => 'category-select',
            'multiselect' => true,
            'showAll' => true
        ];
    }

    /**
     * Whether permission is granted
     *
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->authorization->isAllowed('Magento_Catalog::categories');
    }
}
