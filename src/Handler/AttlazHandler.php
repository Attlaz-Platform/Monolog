<?php
declare(strict_types=1);


namespace Attlaz\AttlazMonolog\Handler;


use Attlaz\AttlazMonolog\Formatter\AttlazFormatter;
use Attlaz\Client;
use Attlaz\Model\LogEntry;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class AttlazHandler extends AbstractProcessingHandler
{
    private $client;
    private $maxLogMessageLength = 5000;
    private $projectKey;
    private $projectEnvironmentKey;


    public function __construct(Client $client, int $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->client = $client;
    }

    public function setClient(Client $attlazClient):void{
        $this->client = $attlazClient;
    }


    public function setProject(string $projectKey, string $projectEnvironmentKey): void
    {
        $this->projectKey = $projectKey;
        $this->projectEnvironmentKey = $projectEnvironmentKey;
    }


    private function recordToLogEntry(array $record): LogEntry
    {
        //TODO: check message length, if its to long, break the message in parts or skip


        $logEntry = new LogEntry($record['message'], strtolower($record['level_name']));
        $logEntry->date = $record['datetime'];
        $logEntry->context = $record['context'];
        if (isset($record['extra']['execution'])) {
            //TODO: what is the log entry type when no task execution is defined?
            //                $logEntry->context['taskexecution'] = $record['extra']['execution'];
            //                $logEntry->type = 'taskexecution';

            $logEntry->tags[] = [
                'key' => 'taskexecution',
                'value' => $record['extra']['execution'],
            ];
            $logEntry->tags[] = [
                'key' => 'type',
                'value' => 'taskexecution',
            ];
        } else {
            if (isset($this->projectKey) && isset($this->projectEnvironmentKey)) {
                $logEntry->tags[] = [
                    'key' => 'project',
                    'value' => $this->projectKey,
                ];
                $logEntry->tags[] = [
                    'key' => 'project_environment',
                    'value' => $this->projectEnvironmentKey,
                ];


                $logEntry->tags[] = [
                    'key' => 'type',
                    'value' => 'project',
                ];
            }


        }

        if (\strlen($logEntry->message) > $this->maxLogMessageLength) {
            $logEntry->message = \substr($logEntry->message, 0, $this->maxLogMessageLength);
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
        try {
            if (isset($record['formatted'])) {
                $record = $record['formatted'];
            }

            $logEntry = $this->recordToLogEntry($record);

            $logEntryId = $this->client->saveLog($logEntry);

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
