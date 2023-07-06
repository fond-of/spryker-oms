<?php

namespace FondOfSpryker\Zed\Oms;

use FondOfSpryker\Shared\Oms\OmsConstants;
use Spryker\Zed\Oms\OmsConfig as SprykerOmsConfig;

class OmsConfig extends SprykerOmsConfig
{
    /**
     * @return array
     */
    public function getWarrantyConditionsUrl(): array
    {
        return $this->get(OmsConstants::WARRANTY_CONDITIONS_URL, []);
    }
}
