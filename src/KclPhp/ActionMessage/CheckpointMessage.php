<?php
namespace KclPhp\ActionMessage;

use KclPhp\Checkpoint\Checkpointer;
use KclPhp\RecordProcessor;

class CheckpointMessage implements ActionMessage {

    const ACTION_NAME = 'checkpoint';

    private $checkpoint;

    private $error;

    public function __construct($checkpoint, $error) {
        $this->checkpoint = $checkpoint;
        $this->error = $error;
    }

    public function getAction() {
        return self::ACTION_NAME;
    }

    public function getCheckpoint() {
        return $this->checkpoint;
    }

    public function getError() {
        return $this->error;
    }

    public function dispatch(Checkpointer $checkpointer, RecordProcessor $recordProcessor) {
        throw new \BadMethodCallException('Cannot dispatch a checkpoint message');
    }

}
