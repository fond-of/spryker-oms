<?php

namespace FondOfSpryker\Zed\Oms\Communication\Plugin\Mail;

use FondOfSpryker\Shared\Customer\CustomerConstants;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CountryTransfer;
use Generated\Shared\Transfer\MailAttachmentTransfer;
use Generated\Shared\Transfer\MailRecipientTransfer;
use Generated\Shared\Transfer\MailTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Orm\Zed\Country\Persistence\SpyCountryQuery;
use Orm\Zed\Country\Persistence\SpyRegionQuery;
use Spryker\Zed\Oms\Communication\Plugin\Mail\OrderConfirmationMailTypeBuilderPlugin as SprykerOrderConfirmationMailTypeBuilderPlugin;

class OrderConfirmationMailTypeBuilderPlugin extends SprykerOrderConfirmationMailTypeBuilderPlugin
{
    public const MAIL_TYPE = 'order confirmation mail';

    /**
     * {@inheritDoc}
     * - Builds the `MailTransfer` with data for an order confirmation mail.
     *
     * @param \Generated\Shared\Transfer\MailTransfer $mailTransfer
     *
     * @return \Generated\Shared\Transfer\MailTransfer
     * @api
     *
     */
    public function build(MailTransfer $mailTransfer): MailTransfer
    {
        $mailTransfer = parent::build($mailTransfer);

        $this->setSubject($mailTransfer);
        $this->addRecipient($mailTransfer);
        $this->setRegionBillingAddress($mailTransfer);
        $this->setRegionShippingAddress($mailTransfer);
        $this->setCountryBillingAddress($mailTransfer);
        $this->setCountryShippingAddress($mailTransfer);
        $this->isBillingAddressInEU($mailTransfer);
        $this->setWarrantyConditions($mailTransfer);

        return $mailTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\MailTransfer $mailTransfer
     *
     * @return void
     */
    protected function addRecipient(MailTransfer $mailTransfer): void
    {
        $orderTransfer = $mailTransfer->getOrder();

        $mailRecipientTransfer = (new MailRecipientTransfer())
            ->setEmail($orderTransfer->getEmail())
            ->setName($orderTransfer->getFirstName() . ' ' . $orderTransfer->getLastName());

        $mailTransfer->addRecipient($mailRecipientTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\MailTransfer $mailTransfer
     *
     * @return void
     */
    protected function setSubject(MailTransfer $mailTransfer): void
    {
        $orderTransfer = $mailTransfer->requireOrder()->getOrder();
        $brand = $this->extractBrandGlossaryKey($orderTransfer);

        $subject = $this->getFactory()->getGlossaryFacade()->translate($brand . '.mail.order_confirmation.subject', [
            '%ref%' => $orderTransfer->getOrderReference(),
        ]);

        $mailTransfer->setSubject($subject);
    }

    /**
     * @param \Generated\Shared\Transfer\MailTransfer $mailTransfer
     *
     * @return void
     */
    protected function setRegionBillingAddress(MailTransfer $mailTransfer): void
    {
        $billingAddress = $mailTransfer->getOrder()->getBillingAddress();

        if ($billingAddress->getFkRegion()) {
            $spyRegion = SpyRegionQuery::create()->findPk(
                $billingAddress->getFkRegion(),
            );

            $billingAddress->setRegion($spyRegion->getIso2Code());
        }
    }

    /**
     * @param \Generated\Shared\Transfer\MailTransfer $mailTransfer
     *
     * @return void
     */
    protected function setRegionShippingAddress(MailTransfer $mailTransfer): void
    {
        $shippingAddress = $this->getShippingAddress($mailTransfer);

        if ($shippingAddress->getFkRegion()) {
            $spyRegion = SpyRegionQuery::create()->findPk(
                $shippingAddress->getFkRegion(),
                null,
            );

            $shippingAddress->setRegion($spyRegion->getIso2Code());
        }
    }

    /**
     * @param \Generated\Shared\Transfer\MailTransfer $mailTransfer
     *
     * @return void
     */
    protected function setCountryBillingAddress(MailTransfer $mailTransfer): void
    {
        $billingAddress = $mailTransfer->getOrder()->getBillingAddress();

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
    }

    /**
     * @param \Generated\Shared\Transfer\MailTransfer $mailTransfer
     *
     * @return void
     */
    protected function setCountryShippingAddress(MailTransfer $mailTransfer): void
    {
        $shippingAddress = $this->getShippingAddress($mailTransfer);

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
    }

    /**
     * @param \Generated\Shared\Transfer\MailTransfer $mailTransfer
     *
     * @return void
     */
    protected function isBillingAddressInEU(MailTransfer $mailTransfer): void
    {
        $billingAddress = $mailTransfer->getOrder()->getBillingAddress();
        $isCountryInEU = in_array($billingAddress->getIso2Code(), CustomerConstants::COUNTRIES_IN_EU);

        $mailTransfer->getOrder()->getBillingAddress()->setIsCountryInEu($isCountryInEU);
    }

    /**
     * @param \Generated\Shared\Transfer\MailTransfer $mailTransfer
     *
     * @return void
     */
    protected function setWarrantyConditions(MailTransfer $mailTransfer): void
    {
        $warrantyConditionsByLocale = $this->getConfig()->getWarrantyConditionsUrl();
        $localeName = $mailTransfer->getLocale()->getLocaleName();

        if (!array_key_exists($localeName, $warrantyConditionsByLocale)) {
            return;
        }

        if (!$warrantyConditionsByLocale[$localeName] || !file_exists($warrantyConditionsByLocale[$localeName])) {
            return;
        }

        $this->addAttachment($mailTransfer, $warrantyConditionsByLocale[$localeName]);
    }

    /**
     * @param \Generated\Shared\Transfer\MailTransfer $mailTransfer
     * @param string $attachmentUrl
     *
     * @return void
     */
    protected function addAttachment(MailTransfer $mailTransfer, string $attachmentUrl): void
    {
        $attachments = $mailTransfer->getAttachments();

        $attachment = (new MailAttachmentTransfer)->setAttachmentUrl($attachmentUrl);

        $attachments->append($attachment);

        $mailTransfer->setAttachments($attachments);
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
     * @param \Generated\Shared\Transfer\MailTransfer $mailTransfer
     *
     * @return \Generated\Shared\Transfer\AddressTransfer|null
     */
    protected function getShippingAddress(MailTransfer $mailTransfer): ?AddressTransfer
    {
        $orderTransfer = $mailTransfer->getOrder();
        $shippingAddress = null;

        if ($orderTransfer === null) {
            return null;
        }

        foreach ($orderTransfer->getItems() as $itemTransfer) {
            if ($itemTransfer->getShipment() !== null && $itemTransfer->getShipment()->getShippingAddress() !== null) {
                $shippingAddress = $itemTransfer->getShipment()->getShippingAddress();

                break;
            }
        }

        // @phpstan-ignore-next-line
        if ($shippingAddress === null && method_exists($orderTransfer, 'getShippingAddress')) {
            $shippingAddress = $orderTransfer->getShippingAddress();
        }

        return $shippingAddress;
    }
}
