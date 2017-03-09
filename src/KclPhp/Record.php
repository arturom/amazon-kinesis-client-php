<?php
namespace KclPhp;

/**
 * Creates a new Record object that represent a single record in Kinesis
 */
class Record {

    public $data;

    public $partitionKey;

    public $sequenceNumber;

    public $subSequenceNumber;

    public $approximateArrivalTimestamp;

    public function __construct($data, $partitionKey, $sequenceNumber, $subSequenceNumber, $approximateArrivalTimestamp)
    {
        $this->data = $data;
        $this->partitionKey = $partitionKey;
        $this->sequenceNumber = $sequenceNumber;
        $this->subSequenceNumber = $subSequenceNumber;
        $this->approximateArrivalTimestamp = $approximateArrivalTimestamp;
    }
}
