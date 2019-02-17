<?php

use DigiTickets\Pay360\Messages\CompletePurchaseResponse;
use DigiTickets\Pay360\Messages\CompletePurchaseRequest;

class CompletePurchaseResponseTest extends \PHPUnit\Framework\TestCase
{
    public function testGettersAndSetters()
    {
        $data = new \scpService_scpQueryResponse();
        $data->scpReference = 'scp reference';
        $errorDetails = new \stdClass();
        $errorDetails->errorId = 'error id';
        $errorDetails->errorMessage = 'error message';
        $paymentResult = new \stdClass();
        $paymentResult->errorDetails = $errorDetails;
        $paymentResult->status = 'SUCCESS';
        $data->paymentResult = $paymentResult;

        $client = Mockery::mock(\Omnipay\Common\Http\ClientInterface::class);
        $request = Mockery::mock(\Symfony\Component\HttpFoundation\Request::class);

        $request = new CompletePurchaseRequest($client, $request);
        $request->setTransactionId('transaction id');

        $response = new CompletePurchaseResponse($request, $data);

        $this->assertInstanceOf(CompletePurchaseResponse::class, $response);
        $this->assertEquals('error message', $response->getMessage());
        $this->assertEquals('error id', $response->getCode());
        $this->assertEquals('transaction id', $response->getTransactionId());
        $this->assertEquals('scp reference', $response->getTransactionReference());
        $this->assertTrue($response->isSuccessful());

        $paymentResult->status = 'ERROR';

        $response = new CompletePurchaseResponse($request, $data);

        $this->assertFalse($response->isSuccessful());
    }
}
