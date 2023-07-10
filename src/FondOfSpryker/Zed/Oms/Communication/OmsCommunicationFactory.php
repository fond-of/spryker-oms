<?php

namespace FondOfSpryker\Zed\Oms\Communication;

use FondOfSpryker\Zed\Oms\Dependency\Facade\OmsToGlossaryFacadeInterface;
use Pyz\Zed\Oms\OmsDependencyProvider;
use Spryker\Zed\Oms\Communication\OmsCommunicationFactory as SprykerOmsCommunicationFactory;

class OmsCommunicationFactory extends SprykerOmsCommunicationFactory
{
    /**
     * @return \FondOfSpryker\Zed\Oms\Dependency\Facade\OmsToGlossaryFacadeInterface
     */
    public function getGlossaryFacade(): OmsToGlossaryFacadeInterface
    {
        return $this->getProvidedDependency(OmsDependencyProvider::FACADE_GLOSSARY);
    }
}
