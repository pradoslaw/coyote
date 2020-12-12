<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePathInWikiAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $attachments = \Coyote\Wiki\Attachment::all();

        foreach ($attachments as $attachment) {
            $path = $this->getPath('attachment/', $attachment->getAttribute('file'));

            $path .= $attachment->getAttribute('file');

            $attachment->file = $path;
            $attachment->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $attachments = \Coyote\Wiki\Attachment::all();

        foreach ($attachments as $attachment) {
            $attachment->file = basename($attachment->file);
            $attachment->save();
        }
    }

    private function getPath($path, $file)
    {
        if (strlen($file) === 17) {
            $timestamp = hexdec(substr($file, 0, 8));
            // as of 15th of Jan, we decided to put files into subdirectories
            if ($timestamp > 1484438400) {
                $path .= substr($file, 0, 2) . '/';
            }
        }

        return $path;
    }
}
