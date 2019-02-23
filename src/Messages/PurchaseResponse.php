<?php

namespace DigiTickets\Pay360\Messages;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{

    /**
     * @var bool
     */
    private $isSuccessful = false;

    /**
     * @var null|string
     */
    private $redirectUrl = null;

    /**
     * @var bool
     */
    private $isRedirect = false;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $transactionRef;

    /**
     * PurchaseResponse constructor.
     *
     * @param PurchaseRequest $purchaseRequest
     * @param \scpService_scpInvokeResponse|\Throwable $scpResponse
     */
    public function __construct(
        PurchaseRequest $purchaseRequest,
        $scpResponse = null
    ) {
        parent::__construct($purchaseRequest, $scpResponse);
        if ($scpResponse instanceof \SoapFault) {
            $this->setCode($scpResponse->faultcode);
            $this->setMessage($scpResponse->faultstring);
        } elseif (!($scpResponse instanceof \Throwable)) {
            if (isset($scpResponse->invokeResult)) {
                $this->setCode($scpResponse->invokeResult->status);
                if ($scpResponse->invokeResult->status == 'SUCCESS') {
                    $this->isRedirect = true;
                    $this->redirectUrl = $scpResponse->invokeResult->redirectUrl;
                } else {
                    $this->setMessage($scpResponse->invokeResult->errorDetails->errorMessage);
                }
            }
        }
        if (isset($scpResponse->scpReference)) {
            $this->transactionRef = $scpResponse->scpReference;
        }
    }

    public function isSuccessful()
    {
        return $this->isSuccessful;
    }

    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    public function getRedirectMethod()
    {
        return 'GET';
    }

    public function getRedirectData()
    {
        return null;
    }

    public function isRedirect()
    {
        return $this->isRedirect;
    }

    public function getTransactionReference()
    {
        return $this->transactionRef;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getCode()
    {
        return $this->code;
    }
}
