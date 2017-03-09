<?php
namespace KclPhp\ActionMessage;

use KclPhp\Checkpoint\Checkpointer;
use KclPhp\RecordProcessor;

class ShutdownMessage implements ActionMessage {

    const ACTION_NAME = 'shutdown';

    const REASON_ZOMBIE = 'ZOMBIE';

    const REASON_TERMINATE = 'TERMINATE';

    private $reason;

    public function __construct($reason) {
        $this->reason = $reason;
    }

    public function getAction() {
        return self::ACTION_NAME;
    }

    public function getReason() {
        return $this->reason;
    }

    public function dispatch(Checkpointer $checkpointer, RecordProcessor $recordProcessor) {
        $recordProcessor->shutdown($checkpointer, $this->reason);
    }

}
