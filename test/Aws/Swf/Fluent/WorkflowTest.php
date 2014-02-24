<?php
use Aws\Common\Aws;
use Aws\Swf\Fluent\Workflow;

class WorkflowTest extends PHPUnit_Framework_TestCase {

  private $serviceBuilder;

  public function setUp() {
    $this->serviceBuilder = Aws::factory();
    $this->serviceBuilder->enableFacades();
  }


  public function testConstructor() {
    $testWorkflowName = 'my-test-workflow';
    $testWorkflowOptions = array('option1' => 'value2');

    $workflow = new Workflow($testWorkflowName, $testWorkflowOptions);

    $this->assertEquals($testWorkflowName, $workflow->getName());
    $this->assertEquals($testWorkflowOptions, $workflow->getOptions());
  }

}