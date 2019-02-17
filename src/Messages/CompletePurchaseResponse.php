<?php

namespace DigiTickets\Pay360\Messages;

use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\AbstractResponse;

class CompletePurchaseResponse extends AbstractResponse
{
    public function getTransactionId()
    {
        /** @var CompletePurchaseRequest|AbstractRequest $request */
        $request = $this->getRequest();
        return $request->getTransactionId();
    }

    public function getTransactionReference()
    {
        if ($this->getData()) {
            return $this->getData()->scpReference;
        }

        return '';
    }

    public function getCode()
    {
        if ($this->getData() && $this->getData()->paymentResult && $this->getData()->paymentResult->errorDetails) {
            return $this->getData()->paymentResult->errorDetails->errorId;
        }
        return null;
    }

    public function getMessage()
    {
        if ($this->getData() && $this->getData()->paymentResult && $this->getData()->paymentResult->errorDetails) {
            return $this->getData()->paymentResult->errorDetails->errorMessage;
        }

        return '';
    }

    public function isSuccessful()
    {
        if ($this->getData() && $this->getData()->paymentResult) {
            return $this->getData()->paymentResult->status == 'SUCCESS';
        }

        return false;
    }
}
