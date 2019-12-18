<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace FondOfSpryker\Zed\Oms\Business\Util;

use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Zed\Oms\Dependency\Facade\OmsToStoreFacadeInterface;
use Spryker\Zed\Oms\Persistence\OmsQueryContainerInterface;
use Spryker\Zed\Oms\Business\Util\Reservation as SprykerReservation;

class Reservation extends SprykerReservation implements ReservationInterface
{
    /**
     * @param string $sku
     *
     * @return void
     */
    public function updateReservationQuantity($sku)
    {
        $currentStoreReservationAmount = $this->sumReservedProductQuantitiesForSku($sku);

        $currentStoreTransfer = $this->storeFacade->getCurrentStore();
        $this->saveReservation($sku, $currentStoreTransfer, $currentStoreReservationAmount);

        $this->handleReservationPlugins($sku);
    }
}
