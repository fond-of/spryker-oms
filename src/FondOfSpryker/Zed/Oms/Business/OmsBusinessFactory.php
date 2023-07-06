<?php

namespace FondOfSpryker\Zed\Oms\Business;

use FondOfSpryker\Zed\Oms\Business\OrderStateMachine\Timeout;
use Spryker\Zed\Oms\Business\OmsBusinessFactory as SprykerOmsBusinessFactory;

// phpcs:disable
/**
 * @method \FondOfSpryker\Zed\Oms\OmsConfig getConfig()
 */
// phpcs:enable
class OmsBusinessFactory extends SprykerOmsBusinessFactory
{
    /**
     * @return \Spryker\Zed\Oms\Business\OrderStateMachine\TimeoutInterface
     */
    public function createOrderStateMachineTimeout()
    {
        return new Timeout(
            $this->getQueryContainer(),
            $this->createTimeoutProcessorCollection(),
            $this->getConfig(),
        );
    }
}
