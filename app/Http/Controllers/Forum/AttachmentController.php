<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\Post\AttachmentRepositoryInterface as Attachment;
use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Illuminate\Http\Request;
use Guzzle\Http\Mimetypes;
use Coyote\Http\Controllers\Controller;
use Storage;

/**
 * Class AttachmentController
 * @package Coyote\Http\Controllers\Forum
 */
class AttachmentController extends Controller
{
    /**
     * @var Attachment
     */
    private $attachment;

    /**
     * @var Forum
     */
    private $forum;

    /**
     * @var Post
     */
    private $post;

    /**
     * @param Attachment $attachment
     * @param Post $post
     * @param Forum $forum
     */
    public function __construct(Attachment $attachment, Post $post, Forum $forum)
    {
        parent::__construct();

        $this->attachment = $attachment;
        $this->forum = $forum;
        $this->post = $post;
    }

    /**
     * Upload file to server
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $this->validate($request, [
            'attachment'  => 'required|max:' . (config('filesystems.upload_max_size') * 1024) . '|mimes:' . config('filesystems.upload_mimes')
        ]);

        if ($request->file('attachment')->isValid()) {
            $fileName = uniqid() . '.' . strtolower($request->file('attachment')->getClientOriginalExtension());

            $path = 'forum/' . $fileName;
            Storage::disk('public')->put($path, file_get_contents($request->file('attachment')->getRealPath()));

            $mime = new Mimetypes();

            $data = [
                'size' => Storage::disk('public')->size($path),
                'mime' => $mime->fromFilename($fileName),
                'file' => $fileName,
                'name' => $request->file('attachment')->getClientOriginalName()
            ];

            $this->attachment->create($data);
            return view('forum.partials.attachment', ['attachment' => $data]);
        }
    }

    /**
     * Paste image from clipboard
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function paste()
    {
        $input = file_get_contents("php://input");

        if (strlen($input) > (config('filesystems.upload_max_size') * 1024 * 1024)) {
            abort(500, 'File is too big');
        }

        $fileName = uniqid() . '.png';
        $path = 'forum/' . $fileName;

        Storage::disk('public')->put($path, file_get_contents('data://' . substr($input, 7)));
        $mime = new Mimetypes();

        $data = [
            'size' => Storage::disk('public')->size($path),
            'mime' => $mime->fromFilename($fileName),
            'file' => $fileName,
            'name' => $fileName
        ];

        $this->attachment->create($data);
        return view('forum.partials.attachment', ['attachment' => $data]);
    }

    /**
     * Download the attachment (or show image)
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($id)
    {
        $attachment = $this->attachment->findOrFail($id);
        $post = $this->post->findOrNew($attachment->post_id, ['id', 'forum_id']);
        $forum = $this->forum->find($post->forum_id);

        if (!$forum->userCanAccess($this->userId)) {
            abort(401, 'Unauthorized');
        }

        set_time_limit(0);

        $attachment->count = $attachment->count + 1;
        $attachment->save();

        $isImage = in_array(pathinfo($attachment->file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']);

        $headers = [
            'Content-Type' => $attachment->mime,
            'Content-Disposition' => (!$isImage ? 'attachment' : 'inline') . '; filename="' . $attachment->name . '"',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Length' => $attachment->size,
            'Cache-control' => 'private',
            'Pragma' => 'private',
            'Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT'
        ];

        if ($isImage) {
            return response()->make(Storage::disk('public')->get('forum/' . $attachment->file), 200, $headers);
        } else {
            return response()->download(public_path('storage/forum/' . $attachment->file), $attachment->name, $headers);
        }
    }
}
