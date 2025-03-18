<?php
/**
 * O2TI Change Order Shipping Method.
 *
 * Copyright © 2025 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\ChangeOrderShippingMethod\Model\Config\Source;

/**
 * Order Statuses source model for Processing state
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class FilterStatus extends \Magento\Sales\Model\Config\Source\Order\Status
{
    /**
     * @var string
     */
    protected $_stateStatuses = \Magento\Sales\Model\Order::STATE_PROCESSING;
}
