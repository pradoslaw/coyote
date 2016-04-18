<?php

namespace Coyote\Http\Factories;

trait FilesystemFactory
{
    /**
     * @param string $disk
     * @return \Illuminate\Contracts\Filesystem\Filesystem;
     */
    private function getFilesystemFactory($disk = null)
    {
        if (!$disk) {
            $disk = config('filesystems.default');
        }
        
        return app('filesystem')->disk($disk);
    }
}
