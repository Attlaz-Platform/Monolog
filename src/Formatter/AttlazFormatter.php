<?php
declare(strict_types=1);


namespace Attlaz\AttlazMonolog\Formatter;


use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;

class AttlazFormatter extends NormalizerFormatter implements FormatterInterface
{

    public function format(LogRecord $record)
    {
        $arrRecord = $record->toArray();

        if (isset($arrRecord['context'])) {
            $arrRecord['context'] = $this->formatContext($arrRecord['context']);
        }

        return $arrRecord;
    }

//    public function formatBatch(array $records)
//    {
//        // TODO: Implement formatBatch() method.
//        return $records;
//    }

    private function formatContext(array $context): array
    {
        $result = [];

        foreach ($context as $key => $value) {
            $result[$key] = $this->normalize($value);
        }

        return $result;
    }

    protected function normalizeException(\Throwable $e, int $depth = 0)
    {
        $data = parent::normalizeException($e);
        if (\is_a($e, '\Attlaz\Project\Exception\RuntimeException')) {
            $data['context'] = $e->getContext();
        }

        return $data;
    }
}
