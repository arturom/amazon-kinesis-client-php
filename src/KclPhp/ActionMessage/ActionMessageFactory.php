<?php
namespace KclPhp\ActionMessage;

use KclPhp\Record;
use stdClass;

class ActionMessageFactory
{

    public static function create(stdClass $message)
    {
        switch ($message->action) {
            case ProcessRecordsMessage::ACTION_NAME:
                //return new ProcessRecordsMessage(self::mapRecords($message->records));
                return new ProcessRecordsMessage($message->records);

            case CheckpointMessage::ACTION_NAME:
                return new CheckpointMessage($message->checkpoint, $message->error);

            case ShutdownMessage::ACTION_NAME:
                return new ShutdownMessage($message->reason);

            case InitializeMessage::ACTION_NAME:
                return new InitializeMessage($message->shardId);

            default:
                throw new \InvalidArgumentException('Invalid message type received');
        }
    }

    public static function mapRecords(array $unparsedRecords)
    {
        $records = [];
        foreach ($unparsedRecords as $record) {
            $records[] = new Record(
                $record->data,
                $record->partitionKey,
                $record->sequenceNumber,
                $record->subSequenceNumber,
                $record->approximateArrivalTimestamp
            );
        }
        return $records;
    }

}
