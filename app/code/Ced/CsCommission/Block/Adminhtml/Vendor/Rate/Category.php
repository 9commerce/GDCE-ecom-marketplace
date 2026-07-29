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
 * @category  Ced
 * @package   Ced_CsCommission
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCommission\Block\Adminhtml\Vendor\Rate;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Category extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getArrayRows()
    {
        /** for category */
        if (null !== $this->arrayRowsCache) {
            return $this->arrayRowsCache;
        }

        $result = [];
        $elementCategory = $this->getElement();

        /** check */
        if (!is_array($elementCategory->getValue()) && $elementCategory->getValue() != '') {
            $element_value_cat = json_decode($elementCategory->getValue(), true);
        } else {
            $element_value_cat = $elementCategory->getValue();
        }

        if ($element_value_cat && is_array($element_value_cat)) {
            foreach ($element_value_cat as $rowId => $row) {
                $rowColumnValues = [];
                foreach ($row as $key => $value) {
                    $row[$key] = $value;
                    $rowColumnValues[$this->_getCellInputElementId($rowId, $key)] = $row[$key];
                }
                $row['_id'] = $rowId;
                $row['column_values'] = $rowColumnValues;
                $result[$rowId] = new \Magento\Framework\DataObject($row);
                $this->_prepareArrayRow($result[$rowId]);
            }
        }
        $this->arrayRowsCache = $result;
        return $this->arrayRowsCache;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $category = $row->getCategory();
        $method = $row->getMethod();
        $options = [];
        $options['option_' . $this->_getCategoryWiseRenderer()->calcOptionHash($category)]
            = 'selected="selected"';
        $options['option_' . $this->_getCalculationMethodRenderer()->calcOptionHash($method)]
            = 'selected="selected"';

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getCategoryWiseRenderer()
    {

        if (!$this->actionRenderer) {
            $this->actionRenderer = $this->getLayout()->createBlock(
                \Ced\CsCommission\Block\Adminhtml\Vendor\Rate\Category\Item::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->actionRenderer->setExtraParams('style="width:90px"');
        }
        return $this->actionRenderer;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getCalculationMethodRenderer()
    {
        if (!$this->defaultRenderer) {
            $this->defaultRenderer = $this->getLayout()->createBlock(
                \Ced\CsCommission\Block\Adminhtml\Vendor\Rate\Method::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->defaultRenderer->setExtraParams('style="width:60px"');
        }
        return $this->defaultRenderer;
    }

    /**
     * Prepare to render
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'category',
            [
                'label' => __('Category'),
                'renderer' => $this->_getCategoryWiseRenderer(),
            ]
        );

        $this->addColumn(
            'method',
            [
                'label' => __('Calculation Method'),
                'renderer' => $this->_getCalculationMethodRenderer(),
            ]
        );

        $this->addColumn(
            'fee',
            [
                'label' => __('Commission Fee'),
                'style' => 'width: 123px;',
            ]
        );

        $this->addColumn(
            'priority',
            [
                'label' => __('Priority'),
                'style' => 'width:53px',
            ]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add New Rate');
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);
        $html .= '<input type="hidden" name="category_dummy" id="' . $element->getHtmlId() . '" />';
        return $html;
    }
}
