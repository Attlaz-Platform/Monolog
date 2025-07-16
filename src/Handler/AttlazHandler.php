<?php
declare(strict_types=1);

namespace Attlaz\AttlazMonolog\Handler;

use Attlaz\AttlazMonolog\Formatter\AttlazFormatter;
use Attlaz\Client;
use Attlaz\Model\Log\LogEntry;
use Attlaz\Model\Log\LogStreamId;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class AttlazHandler extends AbstractProcessingHandler
{
    public const CONTEXT_SKIP = '_skip';
    private Client $client;
    private LogStreamId $logStreamId;
    private int $maxLogMessageLength = 5000;

    public function __construct(Client $client, LogStreamId $logStreamId, Level $level = Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->client = $client;
        $this->logStreamId = $logStreamId;
    }

    public function setClient(Client $attlazClient): void
    {
        $this->client = $attlazClient;
    }

    public function getLogStreamId(): LogStreamId
    {
        return $this->logStreamId;
    }

    public function setLogStreamId(LogStreamId $logStreamId): void
    {
        $this->logStreamId = $logStreamId;
    }

    protected function write(array|LogRecord $record): void
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
            // TODO: log to emergency log or add to queue and try again later
            //echo 'Unable to save log to Attlaz: ' . $ex->getMessage() . PHP_EOL;
        }
    }

    // TODO: implement batch handling



    protected function getDefaultFormatter(): FormatterInterface
    {
        return new AttlazFormatter();
    }
    /**
     * @inheritdoc
     */
    // phpcs:disable InpsydeCodingStandard.CodeQuality.NoAccessors.NoGetter
    private function recordToLogEntry(array $record): LogEntry|null
    {
        if (isset($record['context'][self::CONTEXT_SKIP])) {
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
}
