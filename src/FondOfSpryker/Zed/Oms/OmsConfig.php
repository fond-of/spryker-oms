<?php

namespace FondOfSpryker\Zed\Oms;

use FondOfSpryker\Shared\Oms\OmsConstants;
use Spryker\Zed\Oms\OmsConfig as SprykerOmsConfig;

class OmsConfig extends SprykerOmsConfig
{
    /**
     * @return string|false
     */
    public function getWarrantyConditionsUrl(): ?string
    {
        return $this->get(OmsConstants::WARRANTY_CONDITIONS_URL, false);
    }
}
