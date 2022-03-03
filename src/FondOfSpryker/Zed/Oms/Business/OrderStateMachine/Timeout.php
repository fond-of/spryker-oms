<?php

namespace FondOfSpryker\Zed\Oms\Business\OrderStateMachine;

use Generated\Shared\Transfer\OmsCheckTimeoutsQueryCriteriaTransfer;
use Spryker\Shared\Kernel\Store;
use Spryker\Zed\Oms\Business\OrderStateMachine\OrderStateMachineInterface;
use Spryker\Zed\Oms\Business\OrderStateMachine\Timeout as SprykerTimeout;

class Timeout extends SprykerTimeout implements TimeoutInterface
{
    /**
     * @var int
     */
    protected $countAffectedItems;

    /**
     * @param \Spryker\Zed\Oms\Business\OrderStateMachine\OrderStateMachineInterface $orderStateMachine
     * @param \Generated\Shared\Transfer\OmsCheckTimeoutsQueryCriteriaTransfer|null $omsCheckTimeoutsQueryCriteriaTransfer
     *
     * @return int
     */
    public function checkTimeouts(
        OrderStateMachineInterface $orderStateMachine,
        ?OmsCheckTimeoutsQueryCriteriaTransfer $omsCheckTimeoutsQueryCriteriaTransfer = null
    ) {
        $this->countAffectedItems = 0;
        $orderItems = $this->findItemsWithExpiredTimeouts();

        $this->countAffectedItems = $orderItems->count();

        $groupedOrderItems = $this->groupItemsByEvent($orderItems);
        $groupedOrderItems = $this->filterByActiveStore($groupedOrderItems);

        foreach ($groupedOrderItems as $orderData) {
            foreach ($orderData as $event => $orderItems) {
                $orderStateMachine->triggerEvent($event, $orderItems, []);
            }
        }

        return $this->countAffectedItems;
    }

    /**
     * @param array<int, array<string, array<\Orm\Zed\Sales\Persistence\SpySalesOrderItem>>> $groupedOrderItems
     *
     * @return array
     */
    protected function filterByActiveStore(array $groupedOrderItems): array
    {
        $activeStore = $this->getCurrentStore();
        foreach ($groupedOrderItems as $idSalesOrder => $orderData) {
            $salesOrder = $this->queryContainer->querySalesOrderById($idSalesOrder)->findOneByStore($activeStore);
            if ($salesOrder === null) {
                $this->countAffectedItems -= count($orderData);
                unset($groupedOrderItems[$idSalesOrder]);
            }
        }

        return $groupedOrderItems;
    }

    /**
     * @return string
     */
    protected function getCurrentStore(): string
    {
        return Store::getInstance()->getStoreName();
    }
}
