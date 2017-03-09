<?php
namespace KclPhp\Checkpoint;

use KclPhp\ActionMessage\ActionMessageFactory;
use KclPhp\ActionMessage\CheckpointMessage;
use KclPhp\IOHandler;

class Checkpointer {
    private $ioHandler;

    public function __construct(IOHandler $ioHandler)
    {
        $this->ioHandler = $ioHandler;
    }

    private function getAction()
    {
        $line = $this->ioHandler->readLine();
        return ActionMessageFactory::create($line);
    }

    public function checkpoint($sequenceNumber = null, $subSequenceNumber = null)
    {
        $checkpointMessage = [
            'action' => 'checkpoint',
            'sequenceNumber' => $sequenceNumber,
            'subSequenceNumber' => $subSequenceNumber,
        ];
        $this->ioHandler->writeMessage($checkpointMessage);

        $actionMessage = $this->getAction();

        if ($actionMessage instanceof CheckpointMessage) {
            switch($actionMessage->getError()) {
                case null:
                    break;
                case 'ShutdownException':
                    throw new ShutdownException;
                case 'ThrottlingException(':
                    throw new ThrottlingException;
                default:
                    throw new CheckpointException($actionMessage->getError());
            }
        } else {
            throw new InvalidStateException;
        }
    }
}
