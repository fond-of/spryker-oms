<?php
// phpcs:ignoreFile
namespace FondOfSpryker\Zed\Oms\Communication;

use FondOfSpryker\Zed\Oms\Dependency\Facade\OmsToGlossaryFacadeInterface;
use Pyz\Zed\Oms\OmsDependencyProvider;
use Spryker\Zed\Oms\Communication\OmsCommunicationFactory as SprykerOmsCommunicationFactory;

/**
 * @method \Spryker\Zed\Oms\Persistence\OmsQueryContainerInterface getQueryContainer()
 * @method \FondOfSpryker\Zed\Oms\OmsConfig getConfig()
 * @method \Spryker\Zed\Oms\Business\OmsFacadeInterface getFacade()
 * @method \Spryker\Zed\Oms\Persistence\OmsRepositoryInterface getRepository()
 * @method \Spryker\Zed\Oms\Persistence\OmsEntityManagerInterface getEntityManager()
 */
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
