<?php
/**
 * O2TI Change Order Shipping Method.
 *
 * Copyright © 2025 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\ChangeOrderShippingMethod\Model\Order;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class TabVisibilityChecker
{
    /**
     * Config path for module enabled flag
     */
    const XML_PATH_ENABLED = 'change_order_shipping/general/enabled';
    
    /**
     * Config path for allowed order statuses
     */
    const XML_PATH_ALLOWED_STATUSES = 'change_order_shipping/general/allowed_statuses';
    
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    
    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }
    
    /**
     * Check if the module is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Get allowed order statuses for shipping method change
     *
     * @param int|null $storeId
     * @return array
     */
    public function getAllowedStatuses($storeId = null)
    {
        $statusesString = $this->scopeConfig->getValue(
            self::XML_PATH_ALLOWED_STATUSES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        
        return $statusesString ? explode(',', $statusesString) : [];
    }
    
    /**
     * Check if the tab should be visible for the given order
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return bool
     */
    public function isVisibleForOrder($order)
    {
        if (!$this->isEnabled($order->getStoreId())) {
            return false;
        }
        
        $allowedStatuses = $this->getAllowedStatuses($order->getStoreId());
        if (empty($allowedStatuses)) {
            return false;
        }
        
        return in_array($order->getStatus(), $allowedStatuses);
    }
}
