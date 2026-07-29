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
namespace Ced\CsMarketplace\Controller\Adminhtml\Vproducts;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ced\CsMarketplace\Model\VproductsFactory;

class InlineEdit extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Cms\Api\BlockRepositoryInterface
     */
    protected $blockRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        VproductsFactory $vproductsFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->vproductsFactory = $vproductsFactory->create();
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach ($postItems as $productId => $itemValues) {
                    /** @var \Magento\Cms\Model\Block $block */

                    $checkstatus = $itemValues['check_status'];
                    if(!$itemValues['check_status']){

                        $reason = isset($itemValues['reason'])?$itemValues['reason']:'';
                        $this->vproductsFactory->load($productId, 'product_id')->setReason($reason)->save();
                    }

                    $errors = $this->vproductsFactory->changeVproductStatus(
                        [$productId],
                        $checkstatus
                    );


                    if ($errors['success']) {
                        $this->messageManager->addSuccessMessage(__("Status changed Successfully"));
                    }
                    if ($errors['error']) {
                        $this->messageManager->addErrorMessage(__("Can't process approval/disapproval for the Product.The Product's vendor is disapproved or not exist."));

                            $messages[] = __("Can't process approval/disapproval for the Product.The Product's vendor is disapproved or not exist.");
                            $error = true;

                    }
                     
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add block title to error message
     *
     * @param BlockInterface $block
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithBlockId(BlockInterface $block, $errorText)
    {
        return '[Block ID: ' . $block->getId() . '] ' . $errorText;
    }
}
