<?php

namespace Ced\CsMultiShipping\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Tax\Helper\Data;
use Magento\Directory\Helper\Data as DirectoryHelper;

class TaxHelper implements ArgumentInterface
{
    protected $taxHelper;
    protected $directoryHelper;

    public function __construct(
        Data $taxHelper,
        DirectoryHelper $directoryHelper
    ) {
        $this->taxHelper = $taxHelper;
        $this->directoryHelper = $directoryHelper;
    }

    /**
     * @return Data
     */
    public function getTaxHelper()
    {
        return $this->taxHelper;
    }

    /**
     * @return DirectoryHelper
     */
    public function getDirectoryHelper(){
        return $this->directoryHelper;
    }
}

