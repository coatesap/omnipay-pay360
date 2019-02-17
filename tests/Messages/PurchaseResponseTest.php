<?php

use DigiTickets\Pay360\Messages\PurchaseResponse;

class PurchaseResponseTest extends \PHPUnit\Framework\TestCase
{
    public function testGetters()
    {
        $request = Mockery::mock(\DigiTickets\Pay360\Messages\PurchaseRequest::class);
        $scpResponse= new \scpService_scpInvokeResponse();
        $scpResponse->scpReference = 'transaction reference';
        $invokeResult = new \stdClass();
        $invokeResult->status = 'SUCCESS';
        $invokeResult->redirectUrl = 'redirect url';
        $scpResponse->invokeResult = $invokeResult;

        $purchaseResponse = new PurchaseResponse($request, $scpResponse);

        $this->assertEquals('transaction reference', $purchaseResponse->getTransactionReference());
        $this->assertNull($purchaseResponse->getMessage());
        $this->assertFalse($purchaseResponse->isSuccessful());
        $this->assertTrue($purchaseResponse->isRedirect());
        $this->assertEquals('GET', $purchaseResponse->getRedirectMethod());
        $this->assertEquals('redirect url', $purchaseResponse->getRedirectUrl());
        $this->assertEquals('SUCCESS', $purchaseResponse->getCode());

        $purchaseResponse->setMessage('new message');
        $purchaseResponse->setCode('new code');

        $this->assertEquals('new message', $purchaseResponse->getMessage());
        $this->assertEquals('new code', $purchaseResponse->getCode());

        $invokeResult->status = 'ERROR';
        $errorDetails = new \stdClass();
        $errorDetails->errorMessage = 'error message';
        $invokeResult->errorDetails = $errorDetails;

        $purchaseResponse = new PurchaseResponse($request, $scpResponse);

        $this->assertEquals('error message', $purchaseResponse->getMessage());
        $this->assertFalse($purchaseResponse->isSuccessful());
        $this->assertFalse($purchaseResponse->isRedirect());

        \Mockery::close();
    }
}
