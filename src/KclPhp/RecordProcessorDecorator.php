<?php
namespace KclPhp;

use KclPhp\ActionMessage\ShutdownMessage;
use KclPhp\Checkpoint\Checkpointer;
use KclPhp\Checkpoint\InvalidStateException;
use KclPhp\Checkpoint\ShutdownException;
use KclPhp\Checkpoint\ThrottlingException;
use Psr\Log\LoggerInterface;

class RecordProcessorDecorator implements RecordProcessor
{

    private $maxRetries;

    private $retryWaitTime;

    /**
     * @var RecordProcessor
     */
    private $naiveProcessor;

    private $logger;

    public function __construct(RecordProcessor $recordProcessor, LoggerInterface $logger)
    {
        $this->naiveProcessor = $recordProcessor;
        $this->logger = $logger;
    }

    public function initialize($shardId)
    {
        $this->naiveProcessor->initialize($shardId);
    }


    public function processRecords(array $records, Checkpointer $checkpointer)
    {
        $this->naiveProcessor->processRecords($records, $checkpointer);
        $this->checkpoint($checkpointer);
    }

    /**
     * Checkpoints with retries on retryable exceptions.
     *
     * @param Checkpointer $checkpointer the checkpointer provided to either process_records or shutdown
     * @param string $sequenceNumber the sequence number to checkpoint at
     * @param string $subSequenceNumber the sub sequence number to checkpoint at
     */
    public function checkpoint(Checkpointer $checkpointer, $sequenceNumber = null, $subSequenceNumber = null)
    {
        for ($i = 0; $i < $this->maxRetries; $i++) {
            try {
                $checkpointer->checkpoint($sequenceNumber, $subSequenceNumber);
                return;
            }
            catch (ShutdownException $e) {
                // A ShutdownException indicates that this record processor should be shutdown. This is due to
                // some failover event, e.g. another MultiLangDaemon has taken the lease for this shard.
                $this->logger->notice('Encountered shutdown exception, skipping checkpoint');
                return;
            }
            catch (ThrottlingException $e) {
                // A ThrottlingException indicates that one of our dependencies is is over burdened, e.g. too many
                // dynamo writes. We will sleep temporarily to let it recover.
                if ($this->maxRetries - 1 == $i) {
                    $this->logger->warning("Failed to checkpoint after {$this->maxRetries} attempts, giving up");
                    return;
                } else {
                    $this->logger->notice("Was throttled while checkpointing, will attempt again in {$this->retryWaitTime} seconds");
                }
            }
            catch (InvalidStateException $e) {
                $this->logger->error('MultiLangDaemon reported an invalid state while checkpointing');
            }
            catch (\Exception $e) {
                $this->logger->error('Encountered an error while checkpointing', ['exception'=> $e]);
            }

            sleep($this->retryWaitTime);
        }
    }

    /**
     * Called by a KCLProcess instance to indicate that this record processor should shutdown. After this is called,
     * there will be no more calls to any other methods of this record processor.
     * As part of the shutdown process you must inspect :attr:`amazon_kclpy.messages.ShutdownInput.reason` to
     * determine the steps to take.
     *
     * Shutdown Reason ZOMBIE:
     *   **ATTEMPTING TO CHECKPOINT ONCE A LEASE IS LOST WILL FAIL**
     *   A record processor will be shutdown if it loses its lease.  In this case the KCL will terminate the
     *   record processor.  It is not possible to checkpoint once a record processor has lost its lease.
     *
     * Shutdown Reason TERMINATE:
     *   **THE RECORD PROCESSOR MUST CHECKPOINT OR THE KCL WILL BE UNABLE TO PROGRESS**
     *   A record processor will be shutdown once it reaches the end of a shard. A shard ending indicates that
     *   it has been either split into multiple shards or merged with another shard. To begin processing the new
     *   shard(s) it's required that a final checkpoint occurs.
     *
     * @param Checkpointer $checkpointer
     * @param string $reason
     */
    public function shutdown(Checkpointer $checkpointer, $reason)
    {
        try {
            if ($reason === ShutdownMessage::REASON_TERMINATE) {
                // Checkpointing with no parameter will checkpoint at the
                // largest sequence number reached by this processor on this
                // shard id
                $this->checkpoint($checkpointer);
                $this->logger->info('Was told to terminate, will attempt to checkpoint');
            } else {
                $this->logger->info('Shutting down due to failover. Will not checkpoint');
            }

            $this->naiveProcessor->shutdown($checkpointer, $reason);
        } catch (\Exception $e) {
        }
    }

}
