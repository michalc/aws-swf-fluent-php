<?php
use Aws\Common\Aws;
use Aws\Swf\Fluent\Domain;

class DomainTest extends PHPUnit_Framework_TestCase {

  private $serviceBuilder;

  public function testSetAndGetSwfClient() {
    $domain = new Domain();

    $mockSwfClient = $this->getMockBuilder('Aws\Swf\SwfClient')
            ->disableOriginalConstructor()
            ->getMock();

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

  public function testAddWorkflow() {
    $domain = new Domain();
    $workflowMock = $this->getMockBuilder('Aws\Swf\Fluent\Workflow')
            ->disableOriginalConstructor()
            ->getMock();
    $domain->addWorkflow($workflowMock);

    $workflows = $domain->getWorkflows();
    $this->assertEquals($workflowMock, reset($workflows));
  }

  public function testAddWorkflowByName() {
    $domain = new Domain();
    $testWorkflowName = 'my-test-workflow';
    $testNonExistantWorkflowName = 'my-non-existant-workflow';

    $workflowStub = $this->getMockBuilder('Aws\Swf\Fluent\Workflow')
            ->disableOriginalConstructor()
            ->getMock();
    $workflowStub->expects($this->any())
      ->method('getName')
      ->will($this->returnValue($testWorkflowName));

    $domain->addWorkflow($workflowStub);

    $retrievedWorkflow = $domain->getWorkflow($testWorkflowName);
    $this->assertEquals($workflowStub, $retrievedWorkflow);

    $retrievedWorkflow = $domain->getWorkflow($testNonExistantWorkflowName);
    $this->assertEquals(null, $retrievedWorkflow);
  }

  public function testReturnValueOfAddWorkflow() {
    $domain = new Domain();

    $workflowMock = $this->getMockBuilder('Aws\Swf\Fluent\Workflow')
            ->disableOriginalConstructor()
            ->getMock();    

    $returnValue = $domain->addWorkflow($workflowMock);

    $this->assertEquals($workflowMock, $returnValue);
  }

}