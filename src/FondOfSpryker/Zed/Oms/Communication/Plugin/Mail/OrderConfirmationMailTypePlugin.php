<?php

namespace FondOfSpryker\Zed\Oms\Communication\Plugin\Mail;

use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Zed\Mail\Business\Model\Mail\Builder\MailBuilderInterface;
use Spryker\Zed\Oms\Communication\Plugin\Mail\OrderConfirmationMailTypePlugin as SprykerOrderConfirmationMailTypePlugin;

class OrderConfirmationMailTypePlugin extends SprykerOrderConfirmationMailTypePlugin
{
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
}
