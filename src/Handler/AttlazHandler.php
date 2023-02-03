<?php
declare(strict_types=1);

namespace Attlaz\AttlazMonolog\Handler;

use Attlaz\AttlazMonolog\Formatter\AttlazFormatter;
use Attlaz\Client;
use Attlaz\Model\Log\LogEntry;
use Attlaz\Model\Log\LogStreamId;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class AttlazHandler extends AbstractProcessingHandler
{
    private $client;
    private $logStreamId;
    private $maxLogMessageLength = 5000;

    public const CONTEXT_SKIP = '_skip';


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

    private function recordToLogEntry(array $record): ?LogEntry
    {
        if (isset($record['context']) && isset($record['context'][self::CONTEXT_SKIP])) {
            return null;
        }

        $message = $record['message'];
        if (\strlen($message) > $this->maxLogMessageLength) {
            $message = \substr($message, 0, $this->maxLogMessageLength) . '...';
        }

        $logEntry = new LogEntry($this->logStreamId, $message, strtolower($record['level_name']), $record['datetime']);

        $logEntry->context = $record['context'];


        // TODO: what if 'extra' is already defined?
        if (isset($record['extra']) && \count($record['extra']) > 0) {
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
            if (!\is_null($logEntry)) {
                $savedLogEntry = $this->client->getLogEndpoint()->saveLog($logEntry);
            }
        } catch (\Throwable $ex) {
            // TODO: write this to an emergency log file?
            //echo 'Unable to save log to Attlaz: ' . $ex->getMessage() . PHP_EOL;
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
