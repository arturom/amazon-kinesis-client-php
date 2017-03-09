<?php
namespace KclPhp\ActionMessage;

use KclPhp\Checkpoint\Checkpointer;
use KclPhp\Record;
use KclPhp\RecordProcessor;

class ProcessRecordsMessage implements ActionMessage {

    const ACTION_NAME = 'processRecords';

    /**
     * @var Record[]
     */
    private $records;

    /**
     * ProcessRecordsMessage constructor.
     * @param Record[] $records
     */
    public function __construct(array $records) {
        $this->records = $records;
    }

    public function getAction() {
        return self::ACTION_NAME;
    }

    /**
     * @return Record[]
     */
    public function getRecords() {
        return $this->records;
    }

    public function dispatch(Checkpointer $checkpointer, RecordProcessor $recordProcessor) {
        $recordProcessor->processRecords($this->records, $checkpointer);
    }

}
