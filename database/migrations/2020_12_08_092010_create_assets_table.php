<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsTable extends Migration
{
    use \Coyote\Http\Factories\MediaFactory, SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at')->useCurrent();
            $table->nullableMorphs('content');
            $table->string('name');
            $table->string('path');
            $table->integer('size')->default(0);
            $table->string('mime')->nullable();
            $table->integer('count')->default(0);
        });

        $attachments = \Coyote\Post\Attachment::orderBy('id')->get();
        $seq = 0;

        foreach ($attachments as $attachment) {
            $path = $this->getPath('attachment/', $attachment->file);
            $seq = $attachment->id;

            $path .= $attachment->getAttributeValue('file');

            \Coyote\Models\Asset::forceCreate([
                'id' => $attachment->id,
                'created_at' => $attachment->created_at,
                'path' => $path,
                'name' => $attachment->name,
                'size' => $attachment->size,
                'mime' => $attachment->mime,
                'count' => $attachment->count,
                'content_id' => $attachment->post_id,
                'content_type' => \Coyote\Post::class
            ]);
        }

        unset($attachments);

        $seq++;
        $this->db->statement("SELECT setval('assets_id_seq', $seq, true)");

        $gallery = \Coyote\Firm\Gallery::all();

        foreach ($gallery as $photo) {
            $path = $this->getPath('gallery/', $photo->file);

            $path .= $photo->file;
            $size = 0;

            if (\Illuminate\Support\Facades\Storage::exists($path)) {
                $size = \Illuminate\Support\Facades\Storage::size($path);
            }

            \Coyote\Models\Asset::forceCreate([
                'created_at' => $photo->created_at,
                'path' => $path,
                'name' => basename($path),
                'size' => $size,
                'content_id' => $photo->firm_id,
                'content_type' => \Coyote\Firm::class
            ]);
        }

        unset($gallery);

        $microblogs = \Coyote\Microblog::whereNotNull('media')->get();
        $factory = $this->getMediaFactory();

        foreach ($microblogs as $microblog) {
            $json = json_decode($microblog->media, true);

            if (empty($json['image'])) {
                continue;
            }

            foreach ($json['image'] as $image) {
                $media = $factory->make('attachment', [
                    'file_name' => $image
                ]);

                $path = $this->getPath('attachment/', $media->getFilename());

                $path .= $media->getFilename();
                $size = 0;

                if (\Illuminate\Support\Facades\Storage::exists($path)) {
                    $size = \Illuminate\Support\Facades\Storage::size($path);
                }

                \Coyote\Models\Asset::forceCreate([
                    'created_at' => $microblog->created_at,
                    'path' => $path,
                    'name' => basename($path),
                    'size' => $size,
                    'content_id' => $microblog->id,
                    'content_type' => \Coyote\Microblog::class
                ]);
            }
        }

        unset($microblogs);

        $users = \Coyote\User::whereNotNull('photo')->where('photo', '!=', '')->get();

        foreach ($users as $user) {
            $path = $this->getPath('photo/', $user->photo);
            $path .= $user->photo;

            $user->photo = $path;
            $user->save();
        }

        unset($users);

        $firms = \Coyote\Firm::whereNotNull('logo')->get();

        foreach ($firms as $firm) {
            $path = $this->getPath('logo/', $firm->logo);
            $path .= $firm->logo;

            $firm->logo = $path;
            $firm->save();
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assets');

        $users = \Coyote\User::whereNotNull('photo')->get();

        foreach ($users as $user) {
            $user->photo = basename($user->photo);
            $user->save();
        }

        $firms = \Coyote\Firm::whereNotNull('logo')->get();

        foreach ($firms as $firm) {
            $firm->logo = basename($firm->logo);
            $firm->save();
        }
    }
}
