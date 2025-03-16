<?php

namespace Travelnoord\Logging\Formatter;

use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;
use RuntimeException;
use Travelnoord\Logging\Facades\Ecs;

class ScrubOutputFormatter implements FormatterInterface
{
    private FormatterInterface $formatter;

    public function __construct(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    public function format(LogRecord $record): string
    {
        $formatted = $this->formatter->format($record);

        if (! is_string($formatted)) {
            throw new RuntimeException('Cannot sanitize record of type"' . gettype($formatted) . '" from formatter: ' . get_class($this->formatter));
        }

        return Ecs::scrub($formatted);
    }

    /**
     * @param  array<LogRecord>  $records
     */
    public function formatBatch(array $records): mixed
    {
        $formattedRecords = $this->formatter->formatBatch($records);

        if (! is_array($formattedRecords)) {
            return $formattedRecords;
        }

        $result = [];
        foreach ($formattedRecords as $formatted) {
            $result[] = is_string($formatted) ? Ecs::scrub($formatted) : $formatted;
        }

        return $result;
    }

    public function getFormatter(): FormatterInterface
    {
        return $this->formatter;
    }
}
