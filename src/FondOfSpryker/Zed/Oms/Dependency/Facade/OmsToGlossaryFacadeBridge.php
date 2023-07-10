<?php

namespace FondOfSpryker\Zed\Oms\Dependency\Facade;

use Generated\Shared\Transfer\LocaleTransfer;
use Spryker\Zed\Glossary\Business\GlossaryFacadeInterface;

class OmsToGlossaryFacadeBridge implements OmsToGlossaryFacadeInterface
{
    /**
     * @var GlossaryFacadeInterface
     */
    protected $glossaryFacade;

    /**
     * @param GlossaryFacadeInterface $glossaryFacade
     */
    public function __construct(GlossaryFacadeInterface $glossaryFacade)
    {
        $this->glossaryFacade = $glossaryFacade;
    }

    /**
     * @param string $keyName
     * @param array<string, mixed> $data
     * @param \Generated\Shared\Transfer\LocaleTransfer|null $localeTransfer
     *
     * @return string
     */
    public function translate($keyName, array $data = [], ?LocaleTransfer $localeTransfer = null): string
    {
        return $this->glossaryFacade->translate($keyName, $data, $localeTransfer);
    }
}
