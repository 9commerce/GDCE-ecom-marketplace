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

namespace Ced\CsMarketplace\Ui\Component\Listing;

use Magento\Ui\Component\Control\Action;

/**
 * Class MassAction
 */
class MassAction extends Action
{
    /**
     * Prepare
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        $context = $this->getContext();
        $config = $this->getConfiguration();

        $actions = [];
        foreach ($this->getChildComponents() as $actionComponent) {
            $actionConfig = $actionComponent->getConfiguration();
            if (isset($actionConfig['type'])
                && $actionConfig['type'] == 'delete'
                && $context->getRequestParam('check_status')
            ) {
                $actionConfig['url'] = $context->getUrl(
                    'csmarketplace/vproducts/massDelete',
                    ['check_status' => $context->getRequestParam('check_status')]
                );
            }
            $actions[] = $actionConfig;
        }
        $config['actions'] = $actions;
        $this->setData('config', $config);

    }
}
