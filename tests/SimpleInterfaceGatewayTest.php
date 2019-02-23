<?php

use DigiTickets\Pay360\SimpleInterfaceGateway;
use Omnipay\Tests\GatewayTestCase;

class SimpleInterfaceGatewayTest extends GatewayTestCase
{
    /**
     * @var \DigiTickets\Pay360\SimpleInterfaceGateway
     */
    protected $gateway;

    /**
     * @var array
     */
    protected $options;

    public function setUp()
    {
        parent::setUp();

        $this->gateway = new SimpleInterfaceGateway();

        $this->options = [
            'transactionId' => 123,
            'amount' => '10.00',
            'returnUrl' => 'https://www.example.com/return',
            'cancelUrl' => 'https://www.example.com/cancel',
            'items' => new \Omnipay\Common\ItemBag(
                [
                    new \Omnipay\Common\Item(
                        [
                            'description' => 'item 1',
                            'price' => 10.00,
                            'quantity' => 1,
                        ]
                    ),
                ]
            ),
        ];
    }

    public function testCompletePurchaseSuccess()
    {
        $queryResponse = new \scpService_scpQueryResponse();
        $queryResponse->scpReference = 'scp reference';
        $paymentResult = new \stdClass();
        $paymentResult->status = 'SUCCESS';

        $queryResponse->paymentResult = $paymentResult;
        $scpService = Mockery::mock(\scpService::class);
        $scpService->shouldReceive('scpSimpleQuery')->andReturn($queryResponse);

        $response = $this->gateway->completePurchase(['scpService' => $scpService])->send();
        $this->assertInstanceOf(\DigiTickets\Pay360\Messages\CompletePurchaseResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }

    public function testCompletePurchaseFailure()
    {
        $queryResponse = new \scpService_scpQueryResponse();
        $queryResponse->scpReference = 'scp reference';
        $paymentResult = new \stdClass();
        $paymentResult->errorDetails = new \stdClass();
        $paymentResult->errorDetails->errorId = 'error id';
        $paymentResult->errorDetails->errorMessage = 'error message';
        $paymentResult->status = 'ERROR';

        $queryResponse->paymentResult = $paymentResult;
        $scpService = Mockery::mock(\scpService::class);
        $scpService->shouldReceive('scpSimpleQuery')->andReturn($queryResponse);

        $response = $this->gateway->completePurchase(['scpService' => $scpService])->send();
        $this->assertInstanceOf(\DigiTickets\Pay360\Messages\CompletePurchaseResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('error id', $response->getCode());
        $this->assertEquals('error message', $response->getMessage());
    }

    public function testPurchaseSuccess()
    {
        $scpInvokeResponse = new \scpService_scpInvokeResponse();
        $invokeResponse = new \stdClass();
        $invokeResponse->status = 'SUCCESS';
        $invokeResponse->redirectUrl = 'redirect url';
        $scpInvokeResponse->invokeResult = $invokeResponse;
        $scpInvokeResponse->scpReference = 'scp reference';
        $scpService = Mockery::mock(\scpService::class);
        $scpService->shouldReceive('scpSimpleInvoke')->andReturn($scpInvokeResponse);

        $response = $this->gateway->purchase(
            array_merge(
                $this->options,
                [
                    'scpService' => $scpService,
                    'routingSiteID' => '1231331',
                    'routingScpId' => 24978567,
                ]
            )
        )->send();

        $this->assertInstanceOf(\DigiTickets\Pay360\Messages\PurchaseResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('redirect url', $response->getRedirectUrl());
    }

    public function testPurchaseFailure()
    {
        $scpInvokeResponse = new \scpService_scpInvokeResponse();
        $invokeResponse = new \stdClass();
        $invokeResponse->status = 'ERROR';
        $invokeResponse->redirectUrl = 'redirect url';
        $errorDetails = new \stdClass();
        $errorDetails->errorMessage = 'error message';
        $invokeResponse->errorDetails = $errorDetails;
        $scpInvokeResponse->invokeResult = $invokeResponse;
        $scpInvokeResponse->scpReference = 'scp reference';

        $scpService = Mockery::mock(\scpService::class);
        $scpService->shouldReceive('scpSimpleInvoke')->andReturn($scpInvokeResponse);

        $response = $this->gateway->purchase(
            array_merge(
                $this->options,
                [
                    'scpService' => $scpService,
                    'routingSiteID' => '1231331',
                    'routingScpId' => 24978567,
                ]
            )
        )->send();

        $this->assertInstanceOf(\DigiTickets\Pay360\Messages\PurchaseResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals('error message', $response->getMessage());
    }

    /** @doesNotPerformAssertions */
    public function testDefaultParametersHaveMatchingMethods()
    {
        // No default parameters for this gateway - so skip this inherited test
    }
}
