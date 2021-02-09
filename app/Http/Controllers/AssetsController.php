<?php

namespace Coyote\Http\Controllers;

use Coyote\Http\Requests\AssetRequest;
use Coyote\Models\Asset;
use Coyote\Post;
use Coyote\Services\Assets\Thumbnail;
use Coyote\Services\Assets\Url;
use Coyote\Services\Media\Filters\Opg;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Http\Factory\Guzzle\RequestFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Fusonic\OpenGraph\Consumer;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class AssetsController extends Controller
{
    public function opengraph(Request $request, Connection $db, Thumbnail $thumbnail)
    {
        $this->validate($request, [
            'url' => 'required|url'
        ]);

        $client = new Client(['headers' => ['User-Agent' => 'facebookexternalhit/1.1']]);
        $consumer = new Consumer($client, new RequestFactory());

        try {
            $object = $consumer->loadUrl($request->get('url'));

            if (!count($object->images)) {
                return response("No images to save.", 404);
            }

            $extension = pathinfo(parse_url($object->images[0]->url, PHP_URL_PATH), PATHINFO_EXTENSION);

            $filename = $this->getHumanName($extension);
            $tmpPath = sys_get_temp_dir() . '/' . $filename;

            $db->beginTransaction();

            file_put_contents($tmpPath, file_get_contents($object->images[0]->url));

            $uploadedFile = UploadedFile::createFromBase(new UploadedFile($tmpPath, $filename));
            $path = $uploadedFile->store($this->userId);

            $thumbnail->open($path)->setFilter(new Opg())->store($path);

            $asset = Asset::create([
                'name' => $uploadedFile->getClientOriginalName(),
                'path' => $path,
                'size' => $uploadedFile->getSize(),
                'mime' => $uploadedFile->getMimeType(),
                'metadata' => [
                    'title' => $object->title,
                    'description' => $object->description,
                    'url' => $object->url
                ]
            ]);

            $db->commit();
        } catch (\ErrorException | ConnectException $exception) {
            $db->rollBack();
            logger()->error($exception);

            abort(404);
        } catch (\Exception $exception) {
            $db->rollBack();

            throw $exception;
        } finally {
            @unlink($tmpPath);
        }

        return array_merge($asset->toArray(), ['url' => (string) Url::make($asset)]);
    }

    public function upload(AssetRequest $request)
    {
        $uploadedFile = $request->file('asset');
        $path = $uploadedFile->store($this->userId);

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $asset = Asset::create([
            'name' => $uploadedFile->getClientOriginalName() !== 'blob' ? $uploadedFile->getClientOriginalName() : $this->getHumanName($extension),
            'path' => $path,
            'size' => $uploadedFile->getSize(),
            'mime' => $uploadedFile->getMimeType()
        ]);

        return array_merge($asset->toArray(), ['url' => (string) Url::make($asset)]);
    }

    public function download(Filesystem $filesystem, Asset $asset, string $name = null)
    {
        if ($asset->content_type === Post::class) {
            $this->authorize('access', $asset->content->forum);
        }

        set_time_limit(0);

        $asset->count = $asset->count + 1;
        $asset->save();

        $headers = [
            'Content-Type'        => 'Content-Type: ' . $asset->mime
        ];

        if (!$asset->isImage()) {
            $headers['Content-Disposition'] = 'attachment; filename="'. $asset->name .'"';
        }

        return response()->make($filesystem->get($asset->path), 200, $headers);
    }

    /**
     * @param string $extension
     * @return string
     */
    protected function getHumanName(string $extension)
    {
        return 'screenshot-' . date('YmdHis') . '.' . $extension;
    }
}
