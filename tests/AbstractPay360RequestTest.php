<?php

use DigiTickets\Pay360\AbstractPay360Request;

class AbstractPay360RequestTest extends \PHPUnit\Framework\TestCase
{
    public function testGettersAndSetters()
    {
        $client = Mockery::mock(\Omnipay\Common\Http\ClientInterface::class);
        $request = Mockery::mock(\Symfony\Component\HttpFoundation\Request::class);
        $message = new class($client, $request) extends AbstractPay360Request
        {
            public function getData()
            {

            }

            public function sendData($data)
            {

            }

            public function getCredentials()
            {
                return parent::getCredentials();
            }

            public function getEndpoint()
            {
                return parent::getEndpoint();
            }
        };
        $message->setTestMode(TRUE);
        $message->setCredentialsIdentifier('credentials identifier');
        $message->setRoutingScpId('routing scpid');
        $message->setRoutingSiteId('routing siteid');
        $message->setSecretKey('secret key');
        $message->setSignatureHmacKeyID('hmac key id');
        $message->setSubjectId('subject id');

        $this->assertEquals('credentials identifier', $message->getCredentialsIdentifier());
        $this->assertEquals('routing scpid', $message->getRoutingScpId());
        $this->assertEquals('routing siteid', $message->getRoutingSiteId());
        $this->assertEquals('secret key', $message->getSecretKey());
        $this->assertEquals('hmac key id', $message->getSignatureHmacKeyID());
        $this->assertEquals('subject id', $message->getSubjectId());
        $this->assertInstanceOf(\scpService_credentials::class, $message->getCredentials());
        $this->assertEquals('CapitaPortal', $message->getCredentials()->subject->subjectType);
        $this->assertEquals('subject id', $message->getCredentials()->subject->identifier);
        $this->assertEquals('SCP', $message->getCredentials()->subject->systemCode);
        $this->assertStringStartsWith('DT', $message->getCredentials()->requestIdentification->uniqueReference);
        $this->assertStringStartsWith(date('Ymd'), $message->getCredentials()->requestIdentification->timeStamp);
        $this->assertEquals('Original', $message->getCredentials()->signature->algorithm);
        $this->assertEquals('hmac key id', $message->getCredentials()->signature->hmacKeyID);
        $this->assertEquals('credentials identifier', $message->getCredentials()->identifier);
        $this->assertEquals($message::SERVICE_ENDPOINT_TEST, $message->getEndpoint());
    }

    public function testGenerateDigest()
    {
        $client = Mockery::mock(\Omnipay\Common\Http\ClientInterface::class);
        $request = Mockery::mock(\Symfony\Component\HttpFoundation\Request::class);
        $message = new class($client, $request) extends AbstractPay360Request
        {
            public function getData()
            {

            }

            public function sendData($data)
            {

            }

            public function generateDigest($subject, $requestIdentification, $signature)
            {
                return parent::generateDigest($subject, $requestIdentification, $signature);
            }

            public function getSecretKey()
            {
                return '12341567890ABCDEF';
            }
        };

        $requestIdentification = new \scpService_requestIdentification();
        $requestIdentification->uniqueReference = 'DT1234567859';
        $requestIdentification->timeStamp = '20181002100101';

        $subject = new \scpService_subject();
        $subject->subjectType = \scpService_subjectType::CAPITA_PORTAL;
        $subject->identifier = 'subject id';
        $subject->systemCode = 'SCP';

        $signature = new \scpService_signature();
        $signature->algorithm = \scpService_algorithm::ORIGINAL;
        $signature->hmacKeyID = 'hmac key id';

        $this->assertEquals(
            'W18t58EXXCLxS9f2yTUK4EnB6CVQiW1QfGCUyG6NZGg=',
            $message->generateDigest($subject, $requestIdentification, $signature)
        );
    }
}
