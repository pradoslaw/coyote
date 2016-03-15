<?php

namespace Coyote\Listeners;

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
            if (config('queue.default') !== 'sync') {
                throw $e;
            }
        }
    }
}