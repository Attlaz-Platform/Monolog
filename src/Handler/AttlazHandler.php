<?php
declare(strict_types=1);


namespace Attlaz\AttlazMonolog\Handler;


use Attlaz\AttlazMonolog\Formatter\AttlazFormatter;
use Attlaz\Client;
use Attlaz\Model\LogEntry;
use Attlaz\Model\LogStreamId;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class AttlazHandler extends AbstractProcessingHandler
{
    private Client $client;
    private LogStreamId $logStreamId;
    private int $maxLogMessageLength = 5000;


    public function __construct(Client $client, LogStreamId $logStreamId, int $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->client = $client;
        $this->logStreamId = $logStreamId;
    }

    public function setClient(Client $attlazClient): void
    {
        $this->client = $attlazClient;
    }

    public function setLogStreamId(LogStreamId $logStreamId): void
    {
        $this->logStreamId = $logStreamId;
    }

    public function getLogStreamId(): LogStreamId
    {
        return $this->logStreamId;
    }


    private function recordToLogEntry(array $record): LogEntry
    {


        $message = $record['message'];
        if (\strlen($message) > $this->maxLogMessageLength) {
            $message = \substr($message, 0, $this->maxLogMessageLength) . '...';
        }

        $logEntry = new LogEntry($this->logStreamId, $message, strtolower($record['level_name']), $record['datetime']);

        $logEntry->context = $record['context'];


        // TODO: what if 'extra' is already defined?
        if (isset($record['extra'])) {
            $logEntry->context['extra'] = $record['extra'];
        }
        //TODO: combine extra with context?

        return $logEntry;
    }

// TODO: implement batch handling

    protected function write(array $record): void
    {
        try {
            if (isset($record['formatted'])) {
                $record = $record['formatted'];
            }

            $logEntry = $this->recordToLogEntry($record);

            $savedLogEntry = $this->client->getLogEndpoint()->saveLog($logEntry);

            // echo 'Saved log entry: ' . $logEntryId . \PHP_EOL;
        } catch (\Throwable $ex) {
            //  echo 'Unable to save Log: ' . $ex->getMessage() . PHP_EOL;
            // var_dump(\substr($logEntry->message, 0, 500));

            // echo $ex->getTraceAsString() . \PHP_EOL;

            throw $ex;
        }
    }
    /**
     * {@inheritdoc}
     */
    // phpcs:disable InpsydeCodingStandard.CodeQuality.NoAccessors.NoGetter
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new AttlazFormatter();
    }
}
