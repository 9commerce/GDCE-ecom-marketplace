<?php

namespace Ced\CsImportExport\Ui\Component\Listing\Column;

use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class Actions
 */
class Actions extends Column
{

    /**
     * @var UrlInterface
     */
    private $urlInterface;
    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * Actions constructor.
     * @param EncoderInterface $encoder
     * @param UrlInterface $urlInterface
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        EncoderInterface $encoder,
        UrlInterface $urlInterface,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlInterface = $urlInterface;
        $this->encoder = $encoder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $unlink = $this->encoder->encode($item['image_id']);
                $item[$this->getData('name')] = [
                    'remove' => [
                        'href' => $this->urlInterface->getUrl(
                            'csimportexport/import/unlink',
                            ['unlink' => $unlink]
                        ),
                        'label' => __('Delete')
                    ]
                ];
            }
        }
        return $dataSource;
    }
}
