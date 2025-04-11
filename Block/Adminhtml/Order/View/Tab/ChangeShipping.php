<?php
/**
 * O2TI Change Order Shipping Method.
 *
 * Copyright © 2025 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\ChangeOrderShippingMethod\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Shipping\Model\Config;
use Magento\Framework\Registry;
use O2TI\ChangeOrderShippingMethod\Model\Order\TabVisibilityChecker;

class ChangeShipping extends \O2TI\ChangeOrderShippingMethod\Block\Adminhtml\Order\View\ChangeShipping implements TabInterface
{
    /**
     * @var Registry
     */
    protected $registry;
    
    /**
     * @var TabVisibilityChecker
     */
    protected $tabVisibilityChecker;
    
    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $shippingConfig
     * @param Registry $registry
     * @param TabVisibilityChecker $tabVisibilityChecker
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $shippingConfig,
        Registry $registry,
        TabVisibilityChecker $tabVisibilityChecker,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->tabVisibilityChecker = $tabVisibilityChecker;
        parent::__construct($context, $shippingConfig, $data);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Alterar Método de Envio');
    }
    
    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Alterar Método de Envio');
    }
    
    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        $order = $this->getOrder();
        if (!$order) {
            return false;
        }
        
        if (!$this->tabVisibilityChecker->isVisibleForOrder($order)) {
            return false;
        }
        
        return !$order->isCanceled();
    }
    
    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
    
    /**
     * Get order object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->registry->registry('current_order');
    }
    
    /**
     * Get order ID
     *
     * @return int
     */
    public function getOrderId()
    {
        $order = $this->getOrder();
        return $order ? $order->getId() : null;
    }
}