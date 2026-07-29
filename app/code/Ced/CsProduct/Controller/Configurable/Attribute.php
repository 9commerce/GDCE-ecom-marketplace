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

namespace Ced\CsProduct\Controller\Configurable;

use Magento\Framework\View\Result\PageFactory;
use Laminas\Validator\Regex;

abstract class Attribute extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $entity;

    /**
     * @var \Magento\Catalog\Model\Product\Url
     */
    protected $productUrl;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Attribute constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Eav\Model\Entity $entity
     * @param \Magento\Catalog\Model\Product\Url $productUrl
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        PageFactory $resultPageFactory,
        \Magento\Eav\Model\Entity $entity,
        \Magento\Catalog\Model\Product\Url $productUrl
    ) {
        $this->entity = $entity;
        $this->productUrl = $productUrl;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $this->_entityTypeId = $this->entity->setType(
            \Magento\Catalog\Model\Product::ENTITY
        )->getTypeId();
        return parent::dispatch($request);
    }

    /**
     * @param \Magento\Framework\Phrase|null $title
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function createActionPage($title = null)
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        if ($this->getRequest()->getParam('popup')) {
            if ($this->getRequest()->getParam('product_tab') == 'variations') {
                $resultPage->addHandle(['popup', 'catalog_product_attribute_edit_product_tab_variations_popup']);
            } else {
                $resultPage->addHandle(['popup', 'catalog_product_attribute_edit_popup']);
            }
            $pageConfig = $resultPage->getConfig();
            $pageConfig->addBodyClass('attribute-popup');
        } else {
            $resultPage->addBreadcrumb(__('Catalog'), __('Catalog'))
                ->addBreadcrumb(__('Manage Product Attributes'), __('Manage Product Attributes'))
                ->setActiveMenu('Magento_Catalog::catalog_attributes_attributes');
            if (!empty($title)) {
                $resultPage->addBreadcrumb($title, $title);
            }
        }
        $resultPage->getConfig()->getTitle()->prepend(__('Product Attributes'));
        return $resultPage;
    }

    /**
     * Generate code from label
     *
     * @param string $label
     * @return string
     */
    protected function generateCode($label)
    {
        $code = substr(
            preg_replace(
                '/[^a-z_0-9]/',
                '_',
                $this->productUrl->formatUrlKey($label)
            ),
            0,
            30
        );

        $validatorAttrCode = new Regex(['pattern' => '/^[a-z][a-z_0-9]{0,29}[a-z0-9]$/']);
        if (!$validatorAttrCode->isValid($code)) {
            $code = 'attr_' . ($code ?: substr(hash('sha256', time()), 0, 8));
        }
        return $code;
    }

    /**
     * ACL check
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
