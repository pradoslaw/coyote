<?php

namespace Coyote\Listeners;

use Log;

trait Elasticsearch
{
    /**
     * @param \Closure $closure
     * @throws \Exception
     */
    private function fireJobs(\Closure $closure)
    {
        try {
            $closure();
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['debug' => $e->getTrace()]);
//            if (config('queue.default') !== 'sync') {
                throw $e;
//            }
        }
    }
}