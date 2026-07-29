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
 * @package   Ced_CsImportExport
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsImportExport\Controller\Import;

use Ced\CsMarketplace\Controller\Vendor;
use Ced\CsMarketplace\Model\VendorFactory;
use Ced\CsMarketplace\Helper\Data;
use Ced\CsMarketplace\Helper\Acl;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Url\DecoderInterface;
use Ced\CsImportExport\Block\Export\Image;
use Magento\Framework\Filesystem;

/**
 * Class Unlink
 * @package Ced\CsImportExport\Controller\Import
 */
class Unlink extends Vendor
{

    /**
     * @var DecoderInterface
     */
    protected $decoderInterface;
    /**
     * @var Image
     */
    protected $image;
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Unlink constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param Registry $registry
     * @param JsonFactory $jsonFactory
     * @param Data $csmarketplaceHelper
     * @param Acl $aclHelper
     * @param VendorFactory $vendor
     * @param DecoderInterface $decoderInterface
     * @param Image $image
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        Registry $registry,
        JsonFactory $jsonFactory,
        Data $csmarketplaceHelper,
        Acl $aclHelper,
        VendorFactory $vendor,
        DecoderInterface $decoderInterface,
        Image $image,
        Filesystem $filesystem
    ) {
        $this->decoderInterface = $decoderInterface;
        $this->image = $image;
        $this->filesystem = $filesystem;
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );
    }

    /**
     * Image Uploading
     *
     * @return null
     */
    public function execute()
    {
        $vendorId = $this->session->getVendorId();
        $path = $this->getRequest()->getPost('selected');
        $singlepath = $this->getRequest()->getParam('unlink');
        $singlepath = $this->decoderInterface->decode($singlepath);
        if ($vendorId) {
            if (!$path && $this->getRequest()->getPost('excluded') == "false") {
                $mediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
                    ->getAbsolutePath('import/' . $this->image->VendorId() . '/');
                $data = $this->image->read();
                foreach ($data as $item) {
                    $imageName = explode("/", $item);
                    $str = substr(end($imageName), 0, 2);
                    $imagestring = substr($str, 0, 1) . '/' . substr($str, 1, 2);
                    $imagestring = $imagestring . '/' . end($imageName);
                    $path[] = $mediaPath . $imagestring;
                }
            }
            if (!empty($path) && count($path)>0) {
                try {
                    foreach ($path as $_path) {
                        unlink($_path);
                    }
                    $this->messageManager->addSuccessMessage('Images Has Been Deleted Successfuly');
                    $this->_redirect('csimportexport/import/image');
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    $this->_redirect('csimportexport/import/image');
                }
            } else {
                try {
                    unlink($singlepath);
                    $this->messageManager->addSuccessMessage('Image Has Been Deleted Successfuly');
                    $this->_redirect('csimportexport/import/image');
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    $this->_redirect('csimportexport/import/image');
                }
            }
        }
    }
}
