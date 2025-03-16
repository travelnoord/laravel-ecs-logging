<?php

namespace Travelnoord\Logging\Taps;

use Illuminate\Log\Logger;
use Monolog\Handler\FormattableHandlerInterface;
use Travelnoord\Logging\Formatter\ScrubOutputFormatter;

class AddsScrubbingFormatter
{
    public function __invoke(Logger $logger): void
    {
        $monologLogger = $logger->getLogger();

        if (! $monologLogger instanceof \Monolog\Logger) {
            return;
        }

        foreach ($monologLogger->getHandlers() as $handler) {
            if ($handler instanceof FormattableHandlerInterface) {
                $handler->setFormatter(new ScrubOutputFormatter($handler->getFormatter()));
            }
        }
    }
}
