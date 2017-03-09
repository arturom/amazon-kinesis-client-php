<?php
namespace KclPhp\ActionMessage;

use KclPhp\Checkpoint\Checkpointer;
use KclPhp\RecordProcessor;

interface ActionMessage {

    /**
     * @return string
     */
    public function getAction();

    /**
     * @param Checkpointer $checkpointer
     * @param RecordProcessor $recordProcessor
     * @return void
     */
    public function dispatch(Checkpointer $checkpointer, RecordProcessor $recordProcessor);

}
