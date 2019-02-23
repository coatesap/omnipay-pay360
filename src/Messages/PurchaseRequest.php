<?php

namespace DigiTickets\Pay360\Messages;

use DigiTickets\Pay360\AbstractPay360Request;
use DigiTickets\Pay360\Listener;
use Omnipay\Common\CreditCard;

class PurchaseRequest extends AbstractPay360Request
{
    public function getReturnUrl()
    {
        return $this->getParameter('returnUrl');
    }

    public function setReturnUrl($value)
    {
        return $this->setParameter('returnUrl', $value);
    }

    public function getCancelUrl()
    {
        return $this->getParameter('cancelUrl');
    }

    public function setCancelUrl($value)
    {
        return $this->setParameter('cancelUrl', $value);
    }

    public function getBackUrl()
    {
        return $this->getCancelUrl();
    }

    public function getAmount()
    {
        return $this->getParameter('amount');
    }

    public function setAmount($value)
    {
        return $this->setParameter('amount', $value);
    }

    public function getReference()
    {
        return $this->getParameter('reference');
    }

    public function setReference($value)
    {
        $this->setParameter('reference', $value);
    }

    public function getFundCode()
    {
        return $this->getParameter('fundCode');
    }

    public function setFundCode($value)
    {
        $this->setParameter('fundCode', $value);
    }

    public function getData()
    {
        $this->validate('transactionId', 'returnUrl', 'cancelUrl', 'routingSiteId', 'routingScpId', 'amount', 'items');

        $routing = new \scpService_routing();
        $routing->returnUrl = $this->getReturnUrl();
        $routing->backUrl = $this->getBackUrl();
        $routing->siteId = $this->getRoutingSiteId();
        $routing->scpId = $this->getRoutingScpId();

        $saleSummary = new \scpService_summaryData();
        $saleSummary->description = $this->getTransactionId();
        $saleSummary->amountInMinorUnits = $this->getAmountInteger();

        /** @var \scpService_simpleItem[]|\scpService_items $items */
        $items = [];
        $lineId = 1;
        /** @var \Omnipay\Common\Item $itemBagItem */
        foreach ($this->getItems() as $itemBagItem) {
            $itemSummary = new \scpService_summaryData();
            $itemSummary->description = $itemBagItem->getName();
            $itemSummary->amountInMinorUnits = (int)round(100 * $itemBagItem->getPrice() * $itemBagItem->getQuantity());
            $itemSummary->reference = $this->getReference();

            $lgItemItemDetails = new \scpService_lgItemDetails();
            $lgItemItemDetails->fundCode = $this->getFundCode();

            $item = new \scpService_simpleItem();
            $item->itemSummary = $itemSummary;
            $item->lgItemDetails = $lgItemItemDetails;
            $item->quantity = $itemBagItem->getQuantity();
            $item->lineId = $lineId++;

            $items[] = $item;
        }

        $sale = new \scpService_simpleSale();
        $sale->saleSummary = $saleSummary;
        $sale->items = $items;

        $card = $this->getCard() ?? new CreditCard();

        $address = new \scpService_address();
        if ($card->getBillingAddress1()) {
            $address->address1 = substr($card->getBillingAddress1(), 0, 50);
        }
        if ($card->getBillingAddress2()) {
            $address->address2 = substr($card->getBillingAddress2(), 0, 50);
        }
        if ($card->getBillingCity()) {
            $address->address3 = substr($card->getBillingCity(), 0, 50);
        }
        if ($card->getBillingCountry()) {
            $address->country = substr($card->getBillingCountry(), 0, 50);
        }
        if ($card->getBillingPostcode()) {
            $address->postcode = substr($card->getBillingPostcode(), 0, 10);
        }

        $contact = new \scpService_contact();
        if ($card->getEmail()) {
            $contact->email = substr($card->getEmail(), 0, 255);
        }

        $billingDetails = new \scpService_billingDetails();
        $billingDetails->cardHolderDetails = new \scpService_cardHolderDetails();
        if ($card->getBillingName()) {
            $billingDetails->cardHolderDetails->cardHolderName = $card->getBillingName();
        }
        $billingDetails->cardHolderDetails->address = $address;
        $billingDetails->cardHolderDetails->contact = $contact;

        $scpSimpleInvokeRequest = new \scpService_scpSimpleInvokeRequest();
        $scpSimpleInvokeRequest->credentials = $this->getCredentials();
        $scpSimpleInvokeRequest->requestType = 'payOnly';
        $scpSimpleInvokeRequest->requestId = $this->requestIdentification->uniqueReference;
        $scpSimpleInvokeRequest->routing = $routing;
        $scpSimpleInvokeRequest->panEntryMethod = 'ECOM';
        $scpSimpleInvokeRequest->sale = $sale;
        $scpSimpleInvokeRequest->billing = $billingDetails;

        return $scpSimpleInvokeRequest;
    }

    public function sendData($data)
    {
        /** @var Listener $listener */
        foreach ($this->getGateway()->getListeners() as $listener) {
            $listener->update('purchaseSend', $data);
        }

        try {
            $scpClient = $this->getScpService();
        } catch (\Throwable $t) {
            foreach ($this->getGateway()->getListeners() as $listener) {
                $listener->update('clientException', $t);
            }
            error_log($t->getMessage() . ' ' . $t->getTraceAsString());

            return $this->response = new PurchaseResponse($this, $t);
        }

        try {
            $scpSimpleInvokeResponse = $scpClient->scpSimpleInvoke($data);
        } catch (\Throwable $t) {
            error_log($t->getMessage() . ' ' . $t->getTraceAsString());
            foreach ($this->getGateway()->getListeners() as $listener) {
                $listener->update('purchaseExceptionSend', $scpClient->__getLastRequest());
                $listener->update('purchaseExceptionRcv', $scpClient->__getLastResponse());
            }

            return $this->response = new PurchaseResponse($this, $t);
        }

        foreach ($this->getGateway()->getListeners() as $listener) {
            $listener->update('purchaseReceive', $scpSimpleInvokeResponse);
            $listener->update('purchaseSend', $scpClient->__getLastRequest());
            $listener->update('purchaseRcv', $scpClient->__getLastResponse());
        }

        return $this->response = new PurchaseResponse($this, $scpSimpleInvokeResponse);
    }
}
