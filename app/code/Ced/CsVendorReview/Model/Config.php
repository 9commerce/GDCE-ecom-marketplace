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
 * @package     Ced_CsVendorReview
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsVendorReview\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Transaction;
use Magento\Store\Model\StoreManagerInterface;

class Config extends DataObject
{

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ValueInterface
     */
    protected $_backendModel;

    /**
     * @var Transaction
     */
    protected $_transaction;

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * @var string
     */
    protected $_storeCode;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ValueInterface $backendModel
     * @param Transaction $transaction
     * @param array $data
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ValueInterface $backendModel,
        Transaction $transaction,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_backendModel = $backendModel;
        $this->_transaction = $transaction;
        $this->_storeId=(int)$this->_storeManager->getStore()->getId();
        $this->_storeCode=$this->_storeManager->getStore()->getCode();
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getCurrentStoreConfigValue($path)
    {
        return $this->_scopeConfig->getValue($path, 'store', $this->_storeCode);
    }

    /**
     * @param $path
     * @param $value
     * @throws \Exception
     */
    public function setCurrentStoreConfigValue($path, $value)
    {
        $data = [
            'path' => $path,
            'scope' =>  'stores',
            'scope_id' => $this->_storeId,
            'scope_code' => $this->_storeCode,
            'value' => $value,
        ];

        $this->_backendModel->addData($data);
        $this->_transaction->addObject($this->_backendModel);
        $this->_transaction->save();
    }
}
