<?php

namespace Ced\CsImportExport\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class Thumbnail extends Column
{

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$fieldName . '_src'] = $item['image_url'];
                $item[$fieldName . '_link'] = $item['image_url'];
                $item[$fieldName . '_orig_src'] = $item['image_url'];
            }
        }
        return $dataSource;
    }
}
