<?php

namespace Aws\Swf\Fluent;

use Aws\Swf\Enum;

/**
 * Class Workflow
 * @package Aws\Swf\Fluent
 */
class Workflow implements WorkflowItem {

    const EXECUTE_DECISION_WORKFLOW_TASK_DECISION = 'executeDecisionWorkflowTaskDecision';

    /**
     * @var array
     */
    protected $tasks = array();

    /**
     * @var array
     */
    protected $tasksByType = array();

    /**
     * @var array
     */
    protected $transitions = array();
    /**
     * @var null
     */
    protected $lastTask = null;
    /**
     * @var null
     */
    protected $name = null;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var string
     */
    protected $version = '1.0';

    public function __construct($workflowName, $options) {
        $this->setName($workflowName);
        $this->setOptions($options);
    }

    /**
     * @param string $version
     */
    public function setVersion($version) {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * @param null $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return null
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param array $options
     */
    public function setOptions($options) {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @return null
     */
    public function getId() {
        return $this->getName();
    }

    /**
     * @param array $options
     * @throws Exception
     */
    public function split($options = array()) {
        throw new Exception('Not supported');
    }

    /**
     * @param $uri
     * @param array $options
     * @return $this
     */
    public function to($uri, $options = array()) {
        $task = new WorkflowTask($uri, $options);

        switch ($task->getType()) {
            case WorkflowTask::ACTIVITY_TYPE:
                $this->toActivity($task);
                break;

            case WorkflowTask::CHILD_WORKFLOW_TYPE:
                $this->toChildWorkflow($task);
                break;

            case WorkflowTask::DECISION_TYPE:
                $this->toDecision($task);
        }

        $this->addTask($task);
        $this->setLastTask($task);

        return $this;
    }

    /**
     * @param $uri
     * @param array $options
     * @return $this
     */
    public function from($uri, $options = array()) {
        return $this->to($uri, $options);
    }

    /**
     * @param $uri
     * @param array $options
     */
    public function on($uri, $options = array()) {
        return $this->to($uri, $options);
    }

    /**
     *
     *
     * @param $uri
     * @param array $options
     */
    public function registerTask($uri, $options = array()) {
        $task = new WorkflowTask($uri, $options);

        // on activity complete, complete workflow execution, unless there was another activity added
        $this->addTransition(
            $task, Enum\EventType::ACTIVITY_TASK_COMPLETED,
            $this, Enum\DecisionType::COMPLETE_WORKFLOW_EXECUTION);

        // on activity fail, fail workflow
        $this->addTransition(
            $task, Enum\EventType::ACTIVITY_TASK_FAILED,
            $this, Enum\DecisionType::FAIL_WORKFLOW_EXECUTION);

        $this->addTask($task);

        return $this;
    }

    /**
     * @param $task
     */
    protected function toActivity($task) {
        if (is_null($this->lastTask)) {
            $this->addTransition(
                $this, Enum\EventType::WORKFLOW_EXECUTION_STARTED,
                $task, Enum\DecisionType::SCHEDULE_ACTIVITY_TASK);
        }
        else {
            // schedule current task after previous task complete
            $this->addTransition(
                $this->lastTask, Enum\EventType::ACTIVITY_TASK_COMPLETED,
                $task, Enum\DecisionType::SCHEDULE_ACTIVITY_TASK);
        }

        // on activity complete, complete workflow execution, unless there was another activity added
        $this->addTransition(
            $task, Enum\EventType::ACTIVITY_TASK_COMPLETED,
            $this, Enum\DecisionType::COMPLETE_WORKFLOW_EXECUTION);

        // on activity fail, fail workflow
        $this->addTransition(
            $task, Enum\EventType::ACTIVITY_TASK_FAILED,
            $this, Enum\DecisionType::FAIL_WORKFLOW_EXECUTION);
    }

    /**
     * @param $task
     */
    protected function toDecision($task) {
        $this->toActivity($task);

        $this->addTransition(
            $this->lastTask, Enum\EventType::ACTIVITY_TASK_COMPLETED,
            $task, self::EXECUTE_DECISION_WORKFLOW_TASK_DECISION);

        $this->addTransition(
            $this->lastTask, Enum\EventType::ACTIVITY_TASK_FAILED,
            $task, self::EXECUTE_DECISION_WORKFLOW_TASK_DECISION);
    }

    /**
     * @param $task
     * @throws Exception
     */
    protected function toChildWorkflow($task) {
        throw new Exception('Not supported');
    }

    /**
     * @param WorkflowItem $sourceItem
     * @param $stateHint
     * @param WorkflowItem $targetItem
     * @param $decisionHint
     * @return $this
     */
    protected function addTransition(WorkflowItem $sourceItem, $stateHint, WorkflowItem $targetItem, $decisionType) {
        $stateId = $this->getStateId($sourceItem, $stateHint);

        $decisionHint = new DecisionHint();
        $decisionHint->setItem($targetItem);
        $decisionHint->setDecisionType($decisionType);

        $this->transitions[$stateId] = $decisionHint;
        return $this;
    }

    /**
     * @return array
     */
    public function getTransitions() {
        return $this->transitions;
    }

    /**
     * @param WorkflowItem $item
     * @param $state
     * @return string
     */
    public function getStateId(WorkflowItem $item, $state) {
        $result = null;
        switch ($state) {
            case Enum\EventType::WORKFLOW_EXECUTION_STARTED:
                $result = $state;
                break;
            default:
                $result = implode('_', array($item->getId(), $state));
        }

        return $result;
    }

    /**
     * @param WorkflowItem $item
     * @param $state
     * @return null
     */
    public function getDecisionHint(WorkflowItem $item, $state) {
        $result = null;
        $stateId = $this->getStateId($item, $state);
        if (array_key_exists($stateId, $this->transitions)) {
            $result = $this->transitions[$stateId];
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getKnownStates() {
        return array(
            Enum\EventType::WORKFLOW_EXECUTION_STARTED,
            Enum\EventType::ACTIVITY_TASK_COMPLETED,
            Enum\EventType::ACTIVITY_TASK_FAILED
        );
    }

    /**
     * @param $task
     */
    protected function addTask($task) {
        if (!array_key_exists($task->getType(), $this->tasksByType)) {
            $this->tasksByType[$task->getType()] = array();
        }
        $this->tasksByType[$task->getType()][$task->getId()] = $task;
        $this->tasks[$task->getId()] = $task;
    }

    /**
     * @param $type
     * @return array
     */
    public function getTasksByType($type) {
        $result = array();
        if (array_key_exists($type, $this->tasksByType)) {
            $result = $this->tasksByType[$type];
        }
        return $result;
    }

    /**
     * @param $taskId
     * @return null
     */
    public function getTask($taskId) {
        $result = null;
        if (array_key_exists($taskId, $this->tasks)) {
            $result = $this->tasks[$taskId];
        }
        return $result;
    }

    /**
     * @param null $lastTask
     */
    protected function setLastTask($lastTask) {
        $this->lastTask = $lastTask;
    }

    /**
     * @return null
     */
    protected function getLastTask() {
        return $this->lastTask;
    }
}