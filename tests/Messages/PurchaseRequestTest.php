<?php

use DigiTickets\Pay360\Messages\PurchaseRequest;

class PurchaseRequestTest extends PHPUnit_Framework_TestCase
{
    public function testGetData()
    {
        $client = Mockery::mock(\Guzzle\Http\ClientInterface::class);
        $request = Mockery::mock(\Symfony\Component\HttpFoundation\Request::class);

        $request = new PurchaseRequest($client, $request);
        $request->initialize(
            [
                'amount' => 12.34,
                'returnUrl' => 'https://www.example.com/return',
                'cancelUrl' => 'https://www.example.com/cancel',
                'reference' => 'reference',
                'routingSiteID'=>'1231331',
                'routingScpId'=>24978567
            ]
        );
        $ref = "Hello Ma!";
        $request->setTransactionReference($ref);
        $request->setFundCode(8);
        $request->setItems(
            [
                [
                    'description' => 'item 1',
                    'price' => 10.00,
                    'quantity' => 1,
                ],
            ]
        );

        $this->assertInstanceOf(\scpService_scpSimpleInvokeRequest::class, $request->getData());
        $this->assertEquals('1234', $request->getData()->sale->saleSummary->amountInMinorUnits);
        $this->assertEquals($ref, $request->getData()->sale->saleSummary->description);
        $this->assertInternalType('array', $request->getData()->sale->items);
        $this->assertEquals(1, count($request->getData()->sale->items));
        $this->assertInstanceOf(scpService_simpleItem::class, $request->getData()->sale->items[0]);
        $this->assertEquals(1000, $request->getData()->sale->items[0]->itemSummary->amountInMinorUnits);
        $this->assertEquals(1, $request->getData()->sale->items[0]->quantity);
        $this->assertEquals('reference', $request->getData()->sale->items[0]->itemSummary->reference);
        $this->assertEquals('payOnly', $request->getData()->requestType);
        $this->assertEquals('ECOM', $request->getData()->panEntryMethod);
        $this->assertEquals('https://www.example.com/return', $request->getData()->routing->returnUrl);
        $this->assertEquals('https://www.example.com/cancel', $request->getData()->routing->backUrl);
        $this->assertInstanceOf(scpService_lgItemDetails::class, $request->getData()->sale->items[0]->lgItemDetails);
        $this->assertEquals(8, $request->getData()->sale->items[0]->lgItemDetails->fundCode);
    }
}
