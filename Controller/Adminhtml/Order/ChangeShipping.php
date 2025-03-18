<?php
/**
 * O2TI Change Order Shipping Method.
 *
 * Copyright © 2025 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\ChangeOrderShippingMethod\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use O2TI\ChangeOrderShippingMethod\Model\Order\ChangeShippingMethod;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ChangeShipping extends Action
{
    /**
     * Config path for add comment setting
     */
    const XML_PATH_ADD_COMMENT = 'change_order_shipping/general/add_comment';
    
    /**
     * @var ChangeShippingMethod
     */
    protected $changeShippingMethod;
    
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ChangeShippingMethod $changeShippingMethod
     * @param OrderRepositoryInterface $orderRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        ChangeShippingMethod $changeShippingMethod,
        OrderRepositoryInterface $orderRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->changeShippingMethod = $changeShippingMethod;
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }
    
    /**
     * Check if admin user has access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('O2TI_ChangeOrderShippingMethod::change_shipping');
    }
    
    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $newShippingMethod = $this->getRequest()->getParam('shipping_method');
        $newShippingDescription = $this->getRequest()->getParam('shipping_description');
        
        try {
            $order = $this->orderRepository->get($orderId);
            $storeId = $order->getStoreId();
            
            // Get config setting for adding comments
            $addComment = $this->scopeConfig->isSetFlag(
                self::XML_PATH_ADD_COMMENT,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            
            $result = $this->changeShippingMethod->execute(
                $order,
                $newShippingMethod,
                $newShippingDescription,
                $addComment
            );
            
            if ($result) {
                $this->messageManager->addSuccessMessage(
                    __('O método de envio foi alterado com sucesso para o pedido #%1.', $orderId)
                );
            } else {
                $this->messageManager->addErrorMessage(
                    __('Não foi possível alterar o método de envio para o pedido #%1.', $orderId)
                );
            }

        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e, 
                __('Erro ao alterar o método de envio: %1', $e->getMessage())
            );
        }
        
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        
        return $resultRedirect;
    }
}
