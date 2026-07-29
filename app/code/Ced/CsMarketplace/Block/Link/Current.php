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

namespace Ced\CsMarketplace\Block\Link;


use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Block representing link with two possible states.
 * "Current" state means link leads to URL equivalent to URL of currently displayed page.
 *
 * @method string                          getLabel()
 * @method string                          getPath()
 * @method string                          getTitle()
 * @method null|bool                       getCurrent()
 * @method \Magento\Framework\View\Element\Html\Link\Current setCurrent(bool $value)
 */
class Current extends Template
{

    /**
     * Default path
     *
     * @var DefaultPathInterface
     */
    protected $_defaultPath;

    /**
     * Constructor
     *
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param array $data
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_defaultPath = $defaultPath;
    }

    /**
     * Render block HTML
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml()
    {
        if (false != $this->getTemplate()) {
            return parent::_toHtml();
        }

        $highlight = '';
        if ($this->isCurrent()) {
            $highlight = ' active';
        }

        if (0) {
            $html = '<li id="' . $this->escapeHtml((string)new Phrase($this->getName())) .
                '" class="nav item">';
            $html .= '<i class="' . $this->escapeHtml((string)new Phrase($this->getFontAwesome())) .
                '"></i>';
            $html .= '<span><strong style="margin-left: 3px;">'
                . $this->escapeHtml((string)new Phrase($this->getLabel()))
                . '</strong></span>';


            $childHtml = $this->getChildHtml();
            if (strlen($childHtml) > 0) {
                $html .= '<span class="fa arrow"></span>';
            }

            if (strlen($childHtml) > 0) {
                $html .= $childHtml;
                $html .= '<div class="largeview-submenu">';
                $html .= $childHtml;
                $html .= '</div>';
            }

            $html .= '</a>';
            $html .= '</li>';
        } else {
            $html = '<li id="' . $this->escapeHtml((string)new Phrase($this->getName())) .
                '" class="nav item"><a class="' . $highlight . '" href="' . $this->escapeHtml($this->getHref()) . '"';
            $html .= $this->getTitle()
                ? ' title="' . $this->escapeHtml((string)new Phrase($this->getTitle())) . '"'
                : '';
            $html .= '>';
            $html .= '<i class="' . $this->escapeHtml((string)new Phrase($this->getFontAwesome())) .
                '"></i>';

            if ($this->getIsHighlighted() || strlen($highlight) > 0) {
                $html .= '<span><strong style="margin-left: 3px;">';
            } else {
                $html .= '<span style="margin-left: 3px;">';
            }

            $html .= $this->escapeHtml((string)new Phrase($this->getLabel()));

            if ($this->getIsHighlighted() || strlen($highlight) > 0) {
                $html .= '</strong></span>';
            } else {
                $html .= '</span>';
            }

            $childHtml = $this->getChildHtml();
            if (strlen($childHtml) > 0) {
                $html .= '<span class="fa arrow"></span>';
            }

            $html .= '</a>';

            if (strlen($childHtml) > 0) {
                $html .= $childHtml;
                $html .= '<div class="largeview-submenu">';
                $html .= $childHtml;
                $html .= '</div>';
            }
            $html .= '</li>';
        }

        return $html;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isCurrent()
    {
        $layout = $this->getLayout();
        $containerChilds = $layout->getChildNames($this->getNameInLayout());
        $level = (int)$this->getLevel();
        if (count($containerChilds) > 0) {
            foreach ($containerChilds as $containerChild) {
                foreach ($layout->getChildBlocks($containerChild) as $child) {
                    if ($child->getCurrent() || $child->getUrl($child->getPath()) == $child->getUrl($child->getMca())) {
                        return true;
                    }
                }
            }
        } else {
            return ($level == 1) &&
                ($this->getCurrent() || $this->getUrl($this->getPath()) == $this->getUrl($this->getMca()));
        }
        return false;

    }

    /**
     * Get current mca
     *
     * @return string
     */
    private function getMca()
    {
        $routeParts = [
            'module' => $this->_request->getModuleName(),
            'controller' => $this->_request->getControllerName(),
            'action' => $this->_request->getActionName(),
        ];

        $parts = [];
        foreach ($routeParts as $key => $value) {
            if (!empty($value) && $value != $this->_defaultPath->getPart($key)) {
                $parts[] = $value;
            }
        }
        return implode('/', $parts);
    }

    /**
     * Get href URL
     *
     * @return string
     */
    public function getHref()
    {
        if (strlen($this->getPath()) > 0 && $this->getPath() != '#') {
            return $this->getUrl($this->getPath());
        }
        return '#';
    }
}

