<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace FondOfSpryker\Zed\Oms\Business;

use FondOfSpryker\Zed\Oms\Business\OrderStateMachine\Timeout;
use FondOfSpryker\Zed\Oms\Business\Util\Reservation;
use Spryker\Zed\Oms\Business\OmsBusinessFactory as SprykerOmsBusinessFactory;

/**
 * @method \Spryker\Zed\Oms\OmsConfig getConfig()
 * @method \Spryker\Zed\Oms\Persistence\OmsQueryContainerInterface getQueryContainer()
 */
class OmsBusinessFactory extends SprykerOmsBusinessFactory
{
    /**
     * @return \Spryker\Zed\Oms\Business\Util\ReservationInterface
     */
    public function createUtilReservation()
    {
        return new Reservation(
            $this->createActiveProcessFetcher(),
            $this->getQueryContainer(),
            $this->getReservationHandlerPlugins(),
            $this->getStoreFacade()
        );
    }

    /**
     * @return \Spryker\Zed\Oms\Business\OrderStateMachine\TimeoutInterface
     */
    public function createOrderStateMachineTimeout()
    {
        return new Timeout(
            $this->getQueryContainer()
        );
    }
}
