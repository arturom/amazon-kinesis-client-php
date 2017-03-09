<?php
namespace KclPhp;

use stdClass;
use KclPhp\Checkpoint\Checkpointer;
use KclPhp\ActionMessage\ActionMessage;
use KclPhp\ActionMessage\ActionMessageFactory;

class KCLProcess {

    private $recordProcessor;

    private $checkpointer;

    private $ioHandler;

    public function __construct(RecordProcessor $recordProcessor, Checkpointer $checkpointer, IOHandler $ioHandler) {
        $this->recordProcessor = $recordProcessor;
        $this->checkpointer = $checkpointer;
        $this->ioHandler = $ioHandler;
    }

    private function performAction(ActionMessage $actionMessage) {
        try {
            $actionMessage->dispatch($this->checkpointer, $this->recordProcessor);
        } catch (\Exception $e) {
            $this->ioHandler->writeException($e);
        }
    }

    private function reportDone(ActionMessage $actionMessage) {
        $responseMessage = [
            'action'      => 'status',
            'responseFor' => $actionMessage->getAction(),
        ];
        $this->ioHandler->writeMessage($responseMessage);
    }

    private function handleLine(stdClass $message) {
        $actionMessage = ActionMessageFactory::create($message);
        $this->performAction($actionMessage);
        $this->reportDone($actionMessage);
    }

    public function run() {
        while(true) {
            $line = $this->ioHandler->readLine();
            if($line) {
                $this->handleLine($line);
            }
        }
    }
}
