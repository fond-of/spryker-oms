<?php

namespace FondOfSpryker\Zed\Oms\Dependency\Facade;

use Generated\Shared\Transfer\LocaleTransfer;

interface OmsToGlossaryFacadeInterface
{
    /**
     * @param string $keyName
     * @param array<string, mixed> $data
     * @param \Generated\Shared\Transfer\LocaleTransfer|null $localeTransfer
     *
     * @return string
     */
    public function translate($keyName, array $data = [], ?LocaleTransfer $localeTransfer = null): string;
}
