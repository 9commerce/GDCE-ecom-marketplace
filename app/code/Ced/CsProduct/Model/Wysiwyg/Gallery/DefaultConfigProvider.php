<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsProduct
 * @author   CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

declare(strict_types=1);

namespace Ced\CsProduct\Model\Wysiwyg\Gallery;

class DefaultConfigProvider implements \Magento\Framework\Data\Wysiwyg\ConfigProviderInterface
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $backendUrl;

    /**
     * @var \Ced\CsProduct\Helper\Wysiwyg\Images
     */
    private $imagesHelper;

    /**
     * @var array
     */
    private $windowSize;

    /**
     * @var string|null
     */
    private $currentTreePath;

    /**
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Ced\CsProduct\Helper\Wysiwyg\Images $imagesHelper
     * @param array $windowSize
     * @param string|null $currentTreePath
     */
    public function __construct(
        \Magento\Framework\UrlInterface $backendUrl,
        \Ced\CsProduct\Helper\Wysiwyg\Images $imagesHelper,
        array $windowSize = [],
        $currentTreePath = null
    ) {
        $this->backendUrl = $backendUrl;
        $this->imagesHelper = $imagesHelper;
        $this->windowSize = $windowSize;
        $this->currentTreePath = $currentTreePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(\Magento\Framework\DataObject $config) : \Magento\Framework\DataObject
    {
        $pluginData = (array) $config->getData('plugins');
        $imageData = [
            [
                'name' => 'image',
            ]
        ];

        $fileBrowserUrlParams = [];

        if (is_string($this->currentTreePath)) {
            $fileBrowserUrlParams = [
                'current_tree_path' => $this->imagesHelper->idEncode($this->currentTreePath),
            ];
        }

        return $config->addData(
            [
                'add_images' => true,
                'files_browser_window_url' => $this->backendUrl->getUrl(
                    'csproduct/wysiwyg_images/index',
                    $fileBrowserUrlParams
                ),
                'files_browser_window_width' => $this->windowSize['width'],
                'files_browser_window_height' => $this->windowSize['height'],
                'plugins' => array_merge($pluginData, $imageData)
            ]
        );
    }
}
