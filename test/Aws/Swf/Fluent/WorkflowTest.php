<?php
use Aws\Common\Aws;
use Aws\Swf\Fluent\Workflow;
use Aws\Swf\Fluent\WorkflowTask;

class WorkflowTest extends PHPUnit_Framework_TestCase {

  public function testConstructor() {
    $testWorkflowName = 'my-test-workflow';
    $testWorkflowOptions = array('option1' => 'value2');

    $workflow = new Workflow($testWorkflowName, $testWorkflowOptions);

    $this->assertEquals($testWorkflowName, $workflow->getName());
    $this->assertEquals($testWorkflowOptions, $workflow->getOptions());
  }

   /**
     * @dataProvider testToProvider
     */
  public function testTo($type, $function) {
    $testWorkflowName = 'my-test-workflow';
    $testWorkflowOptions = array('option1' => 'value2');

    $workflow = $this->getMockBuilder('Aws\Swf\Fluent\Workflow')
      ->setConstructorArgs(array($testWorkflowName))
      ->setMethods(array('getType',$function,'addTask','setLastTask'))
      ->getMock();

    $taskStub = $this->getMockBuilder('Aws\Swf\Fluent\WorkflowTask')
      ->disableOriginalConstructor()
      ->getMock();

    $taskStub->expects($this->any())
      ->method('getType')
      ->will($this->returnValue($type));

    $workflow->expects($this->once())
      ->method($function)
      ->with($taskStub);

    $workflow->expects($this->once())
      ->method('addTask')
      ->with($taskStub);

    $workflow->expects($this->once())
      ->method('setLastTask')
      ->with($taskStub);

    $workflow->to($taskStub);
  }

  public function testToProvider() {
    return array(
      array(WorkflowTask::ACTIVITY_TYPE, 'toActivity'),
      array(WorkflowTask::CHILD_WORKFLOW_TYPE, 'toChildWorkflow'),
      array(WorkflowTask::DECISION_TYPE, 'toDecision')
    );
  }

}