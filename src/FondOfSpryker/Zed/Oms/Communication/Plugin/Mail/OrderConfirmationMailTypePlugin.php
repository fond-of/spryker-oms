<?php

namespace FondOfSpryker\Zed\Oms\Communication\Plugin\Mail;

use FondOfSpryker\Shared\Customer\CustomerConstants;
use FondOfSpryker\Zed\Mail\MailConfig;
use Generated\Shared\Transfer\CountryTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Orm\Zed\Country\Persistence\SpyCountryQuery;
use Orm\Zed\Country\Persistence\SpyRegionQuery;
use Spryker\Zed\Mail\Business\Model\Mail\Builder\MailBuilderInterface;
use Spryker\Zed\Oms\Communication\Plugin\Mail\OrderConfirmationMailTypePlugin as SprykerOrderConfirmationMailTypePlugin;

class OrderConfirmationMailTypePlugin extends SprykerOrderConfirmationMailTypePlugin
{
    /**
     * @var \FondOfSpryker\Zed\Mail\MailConfig
     */
    protected $config;

    /**
     * OrderConfirmationMailTypePlugin constructor.
     *
     * @param \FondOfSpryker\Zed\Mail\MailConfig $config
     */
    public function __construct(MailConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @api
     *
     * @param \Spryker\Zed\Mail\Business\Model\Mail\Builder\MailBuilderInterface $mailBuilder
     *
     * @return void
     */
    public function build(MailBuilderInterface $mailBuilder): void
    {
        $this
            ->setSubject($mailBuilder)
            ->setHtmlTemplate($mailBuilder)
            ->setTextTemplate($mailBuilder)
            ->setRecipient($mailBuilder)
            ->setSender($mailBuilder)
            ->setRegionBillingAddress($mailBuilder)
            ->setRegionShippingAddress($mailBuilder)
            ->setCountryBillingAddress($mailBuilder)
            ->setCountryShippingAddress($mailBuilder)
            ->isBillingAddressInEU($mailBuilder);
    }

    /**
     * @param \Spryker\Zed\Mail\Business\Model\Mail\Builder\MailBuilderInterface $mailBuilder
     *
     * @return \FondOfSpryker\Zed\Oms\Communication\Plugin\Mail\OrderConfirmationMailTypePlugin
     */
    protected function setSender(MailBuilderInterface $mailBuilder): self
    {
        $mailBuilder->setSender($this->config->getSenderEmail(), $this->config->getSenderName());

        return $this;
    }

    /**
     * @param \Spryker\Zed\Mail\Business\Model\Mail\Builder\MailBuilderInterface $mailBuilder
     *
     * @return $this
     */
    protected function setSubject(MailBuilderInterface $mailBuilder): self
    {
        /** @var \Generated\Shared\Transfer\OrderTransfer $orderTransfer */
        $orderTransfer = $mailBuilder->getMailTransfer()->requireOrder()->getOrder();
        $brand = $this->extractBrandGlossaryKey($orderTransfer);

        $mailBuilder->setSubject($brand . '.mail.order_confirmation.subject', [
            '%ref%' => $orderTransfer->getOrderReference(),
        ]);

        return $this;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return string
     */
    protected function extractBrandGlossaryKey(OrderTransfer $orderTransfer): string
    {
        if (!$orderTransfer->getStore()) {
            return 'default.';
        }

        $arrStore = explode("_", $orderTransfer->getStore());

        if (is_array($arrStore) && count($arrStore) > 0) {
            return strtolower($arrStore[0]);
        }

        return 'default.';
    }

    /**
     * @TODO Move setting region out of plugin
     *
     * @param \Spryker\Zed\Mail\Business\Model\Mail\Builder\MailBuilderInterface $mailBuilder
     *
     * @return \FondOfSpryker\Zed\Oms\Communication\Plugin\Mail\OrderConfirmationMailTypePlugin
     */
    protected function setRegionBillingAddress(MailBuilderInterface $mailBuilder)
    {
        $billingAddress = $mailBuilder->getMailTransfer()->getOrder()->getBillingAddress();

        if ($billingAddress->getFkRegion()) {
            $spyRegion = SpyRegionQuery::create()->findPk(
                $billingAddress->getFkRegion(),
                null
            );

            $billingAddress->setRegion($spyRegion->getIso2Code());
        }

        return $this;
    }

    /**
     * @TODO Move setting region out of plugin
     *
     * @param \Spryker\Zed\Mail\Business\Model\Mail\Builder\MailBuilderInterface $mailBuilder
     *
     * @return \FondOfSpryker\Zed\Oms\Communication\Plugin\Mail\OrderConfirmationMailTypePlugin
     */
    protected function setRegionShippingAddress(MailBuilderInterface $mailBuilder)
    {
        $shippingAddress = $mailBuilder->getMailTransfer()->getOrder()->getShippingAddress();

        if ($shippingAddress->getFkRegion()) {
            $spyRegion = SpyRegionQuery::create()->findPk(
                $shippingAddress->getFkRegion(),
                null
            );

            $shippingAddress->setRegion($spyRegion->getIso2Code());
        }

        return $this;
    }

    /**
     * @TODO Move setting country out of plugin
     *
     * @param \Spryker\Zed\Mail\Business\Model\Mail\Builder\MailBuilderInterface $mailBuilder
     *
     * @return $this
     */
    protected function setCountryBillingAddress(MailBuilderInterface $mailBuilder)
    {
        $billingAddress = $mailBuilder->getMailTransfer()->getOrder()->getBillingAddress();

        if ($billingAddress->getFkCountry()) {
            $spyCountry = SpyCountryQuery::create()->findPk(
                $billingAddress->getFkCountry(),
                null
            );

            $countryTransfer = new CountryTransfer();
            $countryTransfer->fromArray($spyCountry->toArray());

            $billingAddress->setCountry($countryTransfer);
            $billingAddress->setIso2Code($countryTransfer->getIso2Code());
        }

        return $this;
    }

    /**
     * @TODO Move setting country out of plugin
     *
     * @param \Spryker\Zed\Mail\Business\Model\Mail\Builder\MailBuilderInterface $mailBuilder
     *
     * @return $this
     */
    protected function setCountryShippingAddress(MailBuilderInterface $mailBuilder)
    {
        $shippingAddress = $mailBuilder->getMailTransfer()->getOrder()->getShippingAddress();

        if ($shippingAddress->getFkCountry()) {
            $spyCountry = SpyCountryQuery::create()->findPk(
                $shippingAddress->getFkCountry(),
                null
            );

            $countryTransfer = new CountryTransfer();
            $countryTransfer->fromArray($spyCountry->toArray());

            $shippingAddress->setCountry($countryTransfer);
            $shippingAddress->setIso2Code($countryTransfer->getIso2Code());
        }

        return $this;
    }

    /**
     * @param \Spryker\Zed\Mail\Business\Model\Mail\Builder\MailBuilderInterface $mailBuilder
     *
     * @return \FondOfSpryker\Zed\Oms\Communication\Plugin\Mail\OrderConfirmationMailTypePlugin
     */
    protected function isBillingAddressInEU(MailBuilderInterface $mailBuilder)
    {
        /** @var \Generated\Shared\Transfer\AddressTransfer $billingAddress */
        $billingAddress = $mailBuilder->getMailTransfer()->getOrder()->getBillingAddress();

        $isCountryInEU = (in_array($billingAddress->getIso2Code(), CustomerConstants::COUNTRIES_IN_EU))
            ? true
            : false;

        $mailBuilder->getMailTransfer()->getOrder()->getBillingAddress()->setIsCountryInEu($isCountryInEU);

        return $this;
    }
}
