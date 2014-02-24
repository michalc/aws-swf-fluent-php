<?php
use Aws\Common\Aws;
use Aws\Swf\Fluent\Domain;

class DomainTest extends PHPUnit_Framework_TestCase {

  private $serviceBuilder;

  public function setUp() {
    $this->serviceBuilder = Aws::factory();
    $this->serviceBuilder->enableFacades();
  }

  public function testSetAndGetSwfClient() {
    $domain = new Domain();

    $mockSwfClient = $this->getMockBuilder('Aws\Swf\SwfClient')
            ->disableOriginalConstructor()
            ->getMock();
    //$this->serviceBuilder->set('s3', $mockSwfClient);

    $domain->setSwfClient($mockSwfClient);
    $retrievedSwfClient = $domain->getSwfClient();

    $this->assertEquals($mockSwfClient, $retrievedSwfClient);
  }

  public function testSetAndGetDomainName() {
    $domain = new Domain();
    $testDomainName = 'my-test-domain';

    $domain->setDomainName($testDomainName);
    $retrievedDomainName = $domain->getDomainName();

    $this->assertEquals($testDomainName, $retrievedDomainName);

  }

}