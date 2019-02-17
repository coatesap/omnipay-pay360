<?php

use DigiTickets\Pay360\Messages\CompletePurchaseRequest;

class CompletePurchaseRequestTest extends \PHPUnit\Framework\TestCase
{
    public function testGetData()
    {
        $client = Mockery::mock(\Omnipay\Common\Http\ClientInterface::class);
        $request = Mockery::mock(\Symfony\Component\HttpFoundation\Request::class);

        $request = new CompletePurchaseRequest($client, $request);

        $data = $request->getData();

        $this->assertInstanceOf(scpService_scpQueryRequest::class, $data);
    }
}
