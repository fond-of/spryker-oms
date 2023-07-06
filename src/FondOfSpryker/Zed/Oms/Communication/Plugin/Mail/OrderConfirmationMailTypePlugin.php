<?php

namespace FondOfSpryker\Zed\Oms\Communication\Plugin\Mail;

use FondOfSpryker\Shared\Customer\CustomerConstants;
use Generated\Shared\Transfer\CountryTransfer;
use Generated\Shared\Transfer\MailAttachmentTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Orm\Zed\Country\Persistence\SpyCountryQuery;
use Orm\Zed\Country\Persistence\SpyRegionQuery;
use Spryker\Zed\Mail\Business\Model\Mail\Builder\MailBuilderInterface;
use Spryker\Zed\Mail\MailConfig;
use Spryker\Zed\Oms\Communication\Plugin\Mail\OrderConfirmationMailTypePlugin as SprykerOrderConfirmationMailTypePlugin;

/**
 * @method \FondOfSpryker\Zed\Oms\OmsConfig getConfig()
 */
class OrderConfirmationMailTypePlugin extends SprykerOrderConfirmationMailTypePlugin
{
    /**
     * @var \Spryker\Zed\Mail\MailConfig
     */
    protected $mailConfig;

    /**
     * @param \Spryker\Zed\Mail\MailConfig $mailConfig
     */
    public function __construct(MailConfig $mailConfig)
    {
        $this->mailConfig = $mailConfig;
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
            ->isBillingAddressInEU($mailBuilder)
            ->setWarrantyConditions($mailBuilder);
    }

    /**
     * @param \Spryker\Zed\Mail\Business\Model\Mail\Builder\MailBuilderInterface $mailBuilder
     *
     * @return $this
     */
    protected function setSender(MailBuilderInterface $mailBuilder)
    {
        $mailBuilder->setSender($this->mailConfig->getSenderEmail(), $this->mailConfig->getSenderName());

        return $this;
    }

    /**
     * @param \Spryker\Zed\Mail\Business\Model\Mail\Builder\MailBuilderInterface $mailBuilder
     *
     * @return $this
     */
    protected function setSubject(MailBuilderInterface $mailBuilder)
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

        $arrStore = explode('_', $orderTransfer->getStore());
        // @phpstan-ignore-next-line
        if (count($arrStore) > 0) {
            return strtolower($arrStore[0]);
        }

        // @phpstan-ignore-next-line
        return 'default.';
    }

    /**
     * @TODO Move setting region out of plugin
     *
     * @param \Spryker\Zed\Mail\Business\Model\Mail\Builder\MailBuilderInterface $mailBuilder
     *
     * @return $this
     */
    protected function setRegionBillingAddress(MailBuilderInterface $mailBuilder)
    {
        $billingAddress = $mailBuilder->getMailTransfer()->getOrder()->getBillingAddress();

        if ($billingAddress->getFkRegion()) {
            $spyRegion = SpyRegionQuery::create()->findPk(
                $billingAddress->getFkRegion(),
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
     * @return $this
     */
    protected function setRegionShippingAddress(MailBuilderInterface $mailBuilder)
    {
        $shippingAddress = $this->getShippingAddress($mailBuilder);

        if ($shippingAddress->getFkRegion()) {
            $spyRegion = SpyRegionQuery::create()->findPk(
                $shippingAddress->getFkRegion(),
                null,
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
                null,
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
        $shippingAddress = $this->getShippingAddress($mailBuilder);

        if ($shippingAddress->getFkCountry()) {
            $spyCountry = SpyCountryQuery::create()->findPk(
                $shippingAddress->getFkCountry(),
                null,
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
     * @return $this
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

    /**
     * @param \Spryker\Zed\Mail\Business\Model\Mail\Builder\MailBuilderInterface $mailBuilder
     *
     * @return \Generated\Shared\Transfer\AddressTransfer|null
     */
    protected function getShippingAddress(MailBuilderInterface $mailBuilder)
    {
//ToDo SprykerUpgrade remove workaround!
        $orderTransfer = $mailBuilder->getMailTransfer()->getOrder();
        $shippingAddress = null;
        foreach ($orderTransfer->getItems() as $itemTransfer) {
            if ($shippingAddress !== null) {
                continue;
            }
            if ($itemTransfer !== null && $itemTransfer->getShipment() !== null && $itemTransfer->getShipment()->getShippingAddress() !== null) {
                $shippingAddress = $itemTransfer->getShipment()->getShippingAddress();
            }
        }

        if (
            $shippingAddress === null && $orderTransfer !== null && method_exists(
                $orderTransfer,
                'getShippingAddress',
            )
        ) {
            $shippingAddress = $orderTransfer->getShippingAddress();
        }

        return $shippingAddress;
        //End ToDo
    }

    /**
     * @param \Spryker\Zed\Mail\Business\Model\Mail\Builder\MailBuilderInterface $mailBuilder
     *
     * @return \FondOfSpryker\Zed\Oms\Communication\Plugin\Mail\OrderConfirmationMailTypePlugin
     */
    protected function setWarrantyConditions(MailBuilderInterface $mailBuilder)
    {
        $warrantyConditionsByLocale = $this->getConfig()->getWarrantyConditionsUrl();
        $localeName = $mailBuilder->getMailTransfer()->getLocale()->getLocaleName();

        if (!array_key_exists($localeName, $warrantyConditionsByLocale)) {
            return $this;
        }

        if (!$warrantyConditionsByLocale[$localeName] || !file_exists($warrantyConditionsByLocale[$localeName])) {
            return $this;
        }

        return $this->addAttachment($mailBuilder, $warrantyConditionsByLocale[$localeName]);
    }

    /**
     * @param \Spryker\Zed\Mail\Business\Model\Mail\Builder\MailBuilderInterface $mailBuilder
     * @param string $attachmentUrl
     *
     * @return $this
     */
    protected function addAttachment(MailBuilderInterface $mailBuilder, string $attachmentUrl)
    {
        $attachments = $mailBuilder->getMailTransfer()->getAttachments();

        $attachment = (new MailAttachmentTransfer())->setAttachmentUrl($attachmentUrl);

        $attachments->append($attachment);

        $mailBuilder->getMailTransfer()->setAttachments($attachments);

        return $this;
    }
}
