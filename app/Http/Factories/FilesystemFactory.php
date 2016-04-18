<?php

namespace Coyote\Http\Factories;

trait FilesystemFactory
{
    /**
     * @return \Illuminate\Contracts\Filesystem\Filesystem;
     */
    private function getFilesystemFactory()
    {
        return app('filesystem.disk');
    }
}
