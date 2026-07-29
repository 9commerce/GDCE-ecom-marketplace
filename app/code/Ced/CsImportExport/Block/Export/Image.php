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

namespace Ced\CsImportExport\Block\Export;

/**
 * Class Image
 * @package Ced\CsImportExport\Block\Export
 */
class Image extends \Magento\Backend\Block\Widget\Container
{

    /**
     * Internal constructor
     *
     * @return void
     */
    protected $path;

    /**
     * File factory
     *
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     */
    protected $fileFactory;

    /**
     * Filesystem driver
     *
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    protected $driver;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSesssion;

    /**
     * @var \Magento\Framework\Filesystem
     */
    public $filesystem;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Image constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->filesystem = $filesystem;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Image Uploader');
    }


    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Ced_CsImportExport::export/form/view.phtml');
    }

    /**
     * @return mixed
     */
    public function VendorId()
    {

        return $this->_customerSession->getVendorId();
    }


    /**
     * @return array|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function read()
    {
        $vendor = $this->_customerSession->getVendorId();
        $path = $this->filesystem
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $path = $path->getAbsolutePath('import/' . $vendor . '/');
        $dir = $path;
        $directories = glob($path . '*', GLOB_ONLYDIR);

        if (empty($directories)) {
            return;
        }
        foreach ($directories as $file) {
            if (is_dir($file)) {

                $direc = glob($file . '/*', GLOB_ONLYDIR);

                foreach ($direc as $fl) {

                    $allFileFolder[] = glob($fl . '/*');
                }

            }
        }
        if (empty($allFileFolder)) {
            return;
        }
        $array_meged = [];
        $mediapath = [];
        foreach ($allFileFolder as $key => $images) {
            if (empty($images)) {
                continue;
            }
            foreach ($images as $img) {
                if (empty($img)) {
                    continue;
                }
                $temp = $img;
                $mediapath[] = explode('media/', $temp);
            }

        }
        if (empty($mediapath)) {
            return;
        }
        foreach ($mediapath as $temp) {
            $pathofimage = $temp[1];
            $image[] = $this->_storeManager->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $pathofimage;

        }
        return $image;
    }
}
