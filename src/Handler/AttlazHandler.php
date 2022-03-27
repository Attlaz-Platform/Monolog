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
    private Client $client;
    private int $maxLogMessageLength = 5000;
    private LogStreamId $logStreamId;

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

    private function recordToLogEntry(array $record): LogEntry
    {
        //TODO: check message length, if its to long, break the message in parts or skip

        //echo 'MEM used: ' . \Echron\Tools\Bytes::readable(memory_get_usage(true)) . ' MEM peak: ' . \Echron\Tools\Bytes::readable(memory_get_peak_usage(true)) . \PHP_EOL;


        $message = $record['message'];
        if (\strlen($message) > $this->maxLogMessageLength) {
            $message = \substr($message, 0, $this->maxLogMessageLength) . ' ...';
            // TODO: We should inform that the message is cutted off
        }
        $logEntry = new LogEntry($this->logStreamId, $message, strtolower($record['level_name']), $record['datetime']);

        if (isset($record['context'])) {
        $logEntry->context = $record['context'];
        }

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


        if (\is_null($this->client)) {
            return;
        }
        try {
            if (isset($record['formatted'])) {
                $record = $record['formatted'];
            }

            $logEntry = $this->recordToLogEntry($record);

            $logEntryId = $this->client->getLogEndpoint()->saveLog($logEntry);

        } catch (\Throwable $ex) {
            // TODO: Write this to a panic log?
//            throw  $ex;
//            \var_dump($ex->getMessage());
//            die('--');

            //  echo 'Unable to save Log: ' . $ex->getMessage() . PHP_EOL;
            // var_dump(\substr($logEntry->message, 0, 500));

            // echo $ex->getTraceAsString() . \PHP_EOL;

            // throw $ex;
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
