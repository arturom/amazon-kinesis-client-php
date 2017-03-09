<?php
namespace KclPhp;

use KclPhp\Checkpoint\Checkpointer;

/**
 * Interface for implementing a record processor. A RecordProcessor processes a shard in a stream.
 * Its methods will be called with this pattern:
 * - initialize will be called once
 * - processRecords will be called zero or more times
 * - shutdown will be called if this MultiLangDaemon instance loses the lease to this shard
 */
interface RecordProcessor
{

    /*
     * Called once by a KCLProcess before any calls to processRecords
     *
     * @param string $shardID The shard id that this processor is going to be working on.
     * @return void
     */
    public function initialize($shardID);

    /**
     * Called by a KCLProcess with an array of records to be processed and a checkpointer which accepts sequence numbers
     * from the records to indicate where in the stream to checkpoint.
     *
     * @param Record[] $records: An array of records that are to be processed. A record looks like
     *        {"data":"<base64 encoded string>","partitionKey":"someKey","sequenceNumber":"1234567890"} Note that "data" is a base64
     *        encoded string. You can use base64.b64decode to decode the data into a string. We currently do not do this decoding for you
     *        so as to leave it to your discretion whether you need to decode this particular piece of data.
     * @param Checkpointer $checkpointer: A checkpointer which accepts a sequence number or no parameters.
     */
    public function processRecords(array $records, Checkpointer $checkpointer);

    /**
     * Called by a KCLProcess instance to indicate that this record processor should shutdown. After this is called,
     * there will be no more calls to any other methods of this record processor.
     *
     * @param Checkpointer $checkpointer: A checkpointer which accepts a sequence number or no parameters.
     * @param string $reason: The reason this record processor is being shutdown, either TERMINATE or ZOMBIE. If ZOMBIE,
     *        clients should not checkpoint because there is possibly another record processor which has acquired the lease
     *        for this shard. If TERMINATE then checkpointer.checkpoint() should be called to checkpoint at the end of the
     *        shard so that this processor will be shutdown and new processor(s) will be created to for the child(ren) of
     *        this shard.
     */
    public function shutdown(Checkpointer $checkpointer, $reason);

}
