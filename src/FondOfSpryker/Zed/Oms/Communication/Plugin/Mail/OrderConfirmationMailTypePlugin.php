<?php

namespace FondOfSpryker\Zed\Oms\Communication\Plugin\Mail;

use FondOfSpryker\Shared\Customer\CustomerConstants;
use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Zed\Mail\Business\Model\Mail\Builder\MailBuilderInterface;
use Spryker\Zed\Oms\Communication\Plugin\Mail\OrderConfirmationMailTypePlugin as SprykerOrderConfirmationMailTypePlugin;
use Orm\Zed\Country\Persistence\SpyCountryQuery;

class OrderConfirmationMailTypePlugin extends SprykerOrderConfirmationMailTypePlugin
{
    /**
     * @api
     *
     * @param \Spryker\Zed\Mail\Business\Model\Mail\Builder\MailBuilderInterface $mailBuilder
     *
     * @return void
     */
    public function build(MailBuilderInterface $mailBuilder)
    {
        $this
            ->setSubject($mailBuilder)
            ->setHtmlTemplate($mailBuilder)
            ->setTextTemplate($mailBuilder)
            ->setRecipient($mailBuilder)
            ->setSender($mailBuilder)
            ->isBillingAddressInEU($mailBuilder);
    }

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

    protected function isBillingAddressInEU(MailBuilderInterface &$mailBuilder)
    {
        /** @var \Generated\Shared\Transfer\AddressTransfer $billingAddress */
        $billingAddress = $mailBuilder->getMailTransfer()->getOrder()->getBillingAddress();

        if (!$billingAddress->getIso2Code()) {
            $spyCountry = SpyCountryQuery::create()->findPk(
                $mailBuilder->getMailTransfer()->getOrder()->getBillingAddress()->getFkCountry(),
                null
            );

            $billingAddress->setIso2Code($spyCountry->getIso2Code());
        }

        $isCountryInEU = (in_array($billingAddress->getIso2Code(), CustomerConstants::COUNTRIES_IN_EU))
            ? true
            : false;

        $mailBuilder->getMailTransfer()->getOrder()->getBillingAddress()->setIsCountryInEu($isCountryInEU);
    }
}
