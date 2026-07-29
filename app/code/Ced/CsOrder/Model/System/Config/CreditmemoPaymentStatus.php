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
 * @package     Ced_CsOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsOrder\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;

class CreditmemoPaymentStatus implements ArrayInterface
{
    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $_creditmemoRepository;

    /**
     * CreditmemoPaymentStatus constructor.
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     */
    public function __construct(CreditmemoRepositoryInterface $creditmemoRepository)
    {
        $this->_creditmemoRepository=$creditmemoRepository;
    }

    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        $options=[];
        foreach ($this->_creditmemoRepository->create()->getStates() as $id => $state) {
            $options[] = ['value' => $id, 'label' => __($state->render())];
        }
        return $options;
    }
}
