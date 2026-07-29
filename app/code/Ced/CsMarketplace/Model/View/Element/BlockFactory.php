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

namespace Ced\CsMarketplace\Model\View\Element;

use Magento\Framework\ObjectManagerInterface;


/**
 * Class BlockFactory
 * @package Ced\CsMarketplace\Model\View\Element
 */
class BlockFactory extends \Magento\Framework\View\Element\BlockFactory
{

    const XML_PATH_CED_REWRITES = 'ced/rewrites';
    const CED_THEME = 'Ced/ced_2k18';

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_httpRequest;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $_context;

    /**
     * BlockFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\App\Request\Http $httpRequest
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\App\Request\Http $httpRequest,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->_context = $context;
        $this->_httpRequest = $httpRequest;
        $this->_design = $design;
        parent::__construct($objectManager);
    }

    /**
     * @return string
     */
    public function getVendorPanelTheme(){
        return self::CED_THEME;
    }
    /**
     * Block Factory
     *
     * @param $blockName
     * @param array $arguments
     * @return \Magento\Framework\View\Element\AbstractBlock|\Magento\Framework\View\Element\BlockInterface
     */
    public function createBlock($blockName, array $arguments = [])
    {
        $themeCode = $this->_design->getDesignTheme()->getCode();
        if ($themeCode === $this->getVendorPanelTheme()) {
            $module = $this->_httpRequest->getModuleName();
            $controller = $this->_httpRequest->getControllerName();
            $action = $this->_httpRequest->getActionName();

            $exceptionblocks =
                $this->_context->getScopeConfig()->getValue(self::XML_PATH_CED_REWRITES . "/" . $module . "/" .
                    $controller . "/" . $action);
            if (empty($exceptionblocks)) {
                $action = "all";
                $exceptionblocks =
                    $this->_context->getScopeConfig()->getValue(self::XML_PATH_CED_REWRITES . "/" . $module . "/" .
                        $controller . "/" . $action);
            }

            $block = parent::createBlock($blockName, $arguments);
            $exceptionblocks = explode(",", $exceptionblocks ?? '');
            if (count($exceptionblocks) > 0) {
                foreach ($exceptionblocks as $exceptionblock) {
                    if (strlen($exceptionblock) != 0 && strpos(get_class($block), $exceptionblock) !== false) {
                        $block->setArea('adminhtml');
                    }
                }
            }
            return $block;
        } else {
            return parent::createBlock($blockName, $arguments);
        }
    }
}
