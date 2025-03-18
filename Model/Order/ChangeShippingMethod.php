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

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ChangeShippingMethod
{
    /**
     * Config path for customer notification setting
     */
    const XML_PATH_NOTIFY_CUSTOMER = 'change_order_shipping/general/notify_customer';
    
    /**
     * Config path for add comment setting
     */
    const XML_PATH_ADD_COMMENT = 'change_order_shipping/general/add_comment';
    
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    
    /**
     * @var OrderStatusHistoryInterfaceFactory
     */
    private $historyFactory;
    
    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    private $historyRepository;
    
    /**
     * @var EventManager
     */
    private $eventManager;
    
    /**
     * @var AdminSession
     */
    private $adminSession;
    
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    
    /**
     * Constructor
     *
     * @param ResourceConnection $resourceConnection
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderStatusHistoryInterfaceFactory $historyFactory
     * @param OrderStatusHistoryRepositoryInterface $historyRepository
     * @param EventManager $eventManager
     * @param AdminSession $adminSession
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        OrderRepositoryInterface $orderRepository,
        OrderStatusHistoryInterfaceFactory $historyFactory,
        OrderStatusHistoryRepositoryInterface $historyRepository,
        EventManager $eventManager,
        AdminSession $adminSession,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->orderRepository = $orderRepository;
        $this->historyFactory = $historyFactory;
        $this->historyRepository = $historyRepository;
        $this->eventManager = $eventManager;
        $this->adminSession = $adminSession;
        $this->scopeConfig = $scopeConfig;
    }
    
    /**
     * Check if comments should be added from config
     *
     * @param int $storeId
     * @return bool
     */
    private function shouldAddComment($storeId)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ADD_COMMENT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Check if customer should be notified from config
     *
     * @param int $storeId
     * @return bool
     */
    private function shouldNotifyCustomer($storeId)
    {
        if (!$this->shouldAddComment($storeId)) {
            return false;
        }
        
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_NOTIFY_CUSTOMER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Executa a alteração do método de envio em um pedido existente
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param string $shippingMethod
     * @param string $shippingDescription
     * @param bool $addComment
     * @return bool
     */
    public function execute(
        $order,
        $shippingMethod,
        $shippingDescription,
        $addComment = false
    ) {
        $connection = $this->resourceConnection->getConnection();
        $orderId = $order->getId();
        $storeId = $order->getStoreId();
        
        try {
            $oldShippingMethod = $order->getShippingMethod();
            $oldShippingDescription = $order->getShippingDescription();
            
            $connection->beginTransaction();
            
            $updated = $this->updateOrderTables(
                $connection,
                $orderId,
                $shippingMethod,
                $shippingDescription
            );
            
            $shouldAddComment = $addComment || $this->shouldAddComment($storeId);
            
            if ($updated && $shouldAddComment) {
                $shouldNotifyCustomer = $this->shouldNotifyCustomer($storeId);
                
                $this->addOrderComment(
                    $order,
                    $oldShippingMethod,
                    $oldShippingDescription,
                    $shippingMethod,
                    $shippingDescription,
                    $shouldNotifyCustomer
                );
            }
            
            if ($updated) {
                $connection->commit();
                return true;
            } else {
                $connection->rollBack();
                return false;
            }

        } catch (\Exception $e) {
            if ($connection->getTransactionLevel() > 0) {
                $connection->rollBack();
            }
            return false;
        }
    }
    
    /**
     * Atualiza as tabelas principais do pedido
     *
     * @param AdapterInterface $connection
     * @param int $orderId
     * @param string $shippingMethod
     * @param string $shippingDescription
     * @return bool
     */
    private function updateOrderTables(
        AdapterInterface $connection,
        $orderId,
        $shippingMethod,
        $shippingDescription
    ) {
        $salesOrderTable = $this->resourceConnection->getTableName('sales_order');
        $orderUpdated = $connection->update(
            $salesOrderTable,
            [
                'shipping_method' => $shippingMethod,
                'shipping_description' => $shippingDescription
            ],
            ['entity_id = ?' => $orderId]
        );
        
        $orderGridTable = $this->resourceConnection->getTableName('sales_order_grid');
        $connection->update(
            $orderGridTable,
            [
                'shipping_information' => $shippingDescription
            ],
            ['entity_id = ?' => $orderId]
        );
        
        return $orderUpdated;
    }
    
    /**
     * Get current admin username
     *
     * @return string
     */
    private function getAdminUsername()
    {
        $adminUser = $this->adminSession->getUser();
        if ($adminUser) {
            return $adminUser->getFirstName();
        }
        
        return 'unknown';
    }
    
    /**
     * Adiciona um comentário ao histórico do pedido usando a API
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param string $oldShippingMethod
     * @param string $oldShippingDescription
     * @param string $newShippingMethod
     * @param string $newShippingDescription
     * @param bool $notifyCustomer
     * @return void
     */
    private function addOrderComment(
        $order,
        $oldShippingMethod,
        $oldShippingDescription,
        $newShippingMethod,
        $newShippingDescription,
        $notifyCustomer = false
    ) {
        $adminUsername = $this->getAdminUsername();
        
        $comment = sprintf(
            'O método de envio foi alterado de "%s" ("%s") para "%s" ("%s"). Alterado por: %s.',
            $oldShippingMethod,
            $oldShippingDescription,
            $newShippingMethod,
            $newShippingDescription,
            $adminUsername
        );
        
        $history = $this->historyFactory->create();
        $history->setParentId($order->getEntityId())
            ->setComment($comment)
            ->setStatus($order->getStatus())
            ->setEntityName('order')
            ->setIsCustomerNotified($notifyCustomer)
            ->setIsVisibleOnFront($notifyCustomer);
        
        $this->historyRepository->save($history);
    }
}