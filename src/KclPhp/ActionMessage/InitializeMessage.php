<?php
namespace KclPhp\ActionMessage;

use KclPhp\Checkpoint\Checkpointer;
use KclPhp\RecordProcessor;

class InitializeMessage implements ActionMessage {

    const ACTION_NAME = 'initialize';

    private $shardId;

    public function __construct($shardId) {
        $this->shardId = $shardId;
    }

    public function getAction() {
        return self::ACTION_NAME;
    }

    public function getShardID() {
        return $this->shardId;
    }

    public function dispatch(Checkpointer $checkpointer, RecordProcessor $recordProcessor) {
        $recordProcessor->initialize($this->shardId);
    }

}
