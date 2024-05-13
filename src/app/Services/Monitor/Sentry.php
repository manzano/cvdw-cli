<?php

namespace Manzano\CvdwCli\Services\Monitor;

class Sentry
{
    public function getTracesSampler(): callable
    {
        return function (): void {
            // return a number between 0 and 1
        };
    }
}
