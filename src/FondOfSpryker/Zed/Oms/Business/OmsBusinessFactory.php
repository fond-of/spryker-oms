<?php

namespace FondOfSpryker\Zed\Oms\Business;

use FondOfSpryker\Zed\Oms\Business\OrderStateMachine\Timeout;
use Spryker\Zed\Oms\Business\OmsBusinessFactory as SprykerOmsBusinessFactory;

/**
 * @method \Spryker\Zed\Oms\OmsConfig getConfig()
 * @method \Spryker\Zed\Oms\Persistence\OmsQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\Oms\Persistence\OmsRepositoryInterface getRepository()
 * @method \Spryker\Zed\Oms\Persistence\OmsEntityManagerInterface getEntityManager()
 */
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
