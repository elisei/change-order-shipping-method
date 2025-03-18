<?php
/**
 * O2TI Change Order Shipping Method.
 *
 * Copyright © 2025 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\ChangeOrderShippingMethod\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Shipping\Model\Config;

class ChangeShipping extends Template
{
    /**
     * @var Config
     */
    protected $shippingConfig;
    
    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $shippingConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $shippingConfig,
        array $data = []
    ) {
        $this->shippingConfig = $shippingConfig;
        parent::__construct($context, $data);
    }
    
    /**
     * Get order ID
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }
    
    /**
     * Get available shipping methods
     *
     * @return array
     */
    public function getShippingMethods()
    {
        $methods = [];
        $activeCarriers = $this->shippingConfig->getAllCarriers();
        
        foreach ($activeCarriers as $carrierCode => $carrierModel) {
            if ($carrierModel->isActive()) {
                $carrierMethods = $carrierModel->getAllowedMethods();
                if ($carrierMethods) {
                    foreach ($carrierMethods as $methodCode => $methodTitle) {
                        $code = $carrierCode . '_' . $methodCode;
                        $methods[$code] = $carrierModel->getConfigData('title') . ' - ' . $methodTitle;
                    }
                }
            }
        }
        
        return $methods;
    }
}