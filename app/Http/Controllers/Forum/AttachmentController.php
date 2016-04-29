<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Factories\MediaFactory;
use Coyote\Http\Forms\Forum\AttachmentForm;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\Post\AttachmentRepositoryInterface as Attachment;
use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Illuminate\Http\Request;
use Guzzle\Http\Mimetypes;
use Coyote\Http\Controllers\Controller;

/**
 * Class AttachmentController
 * @package Coyote\Http\Controllers\Forum
 */
class AttachmentController extends Controller
{
    use MediaFactory;

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
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function upload(Request $request)
    {
        $this->validate($request, [
            'attachment'  => sprintf(
                'required|max:%s|mimes:%s',
                config('filesystems.upload_max_size') * 1024,
                config('filesystems.upload_mimes')
            )
        ]);

        $media = $this->getMediaFactory('attachment')->upload($request->file('attachment'));
        $mime = new Mimetypes();

        $data = [
            'size' => $media->size(),
            'file' => $media->getFilename(),
            'name' => $media->getName(),
            'mime' => $mime->fromFilename($media->path())
        ];

        $attachment = $this->attachment->create($data);
        return $this->renderForm($attachment);
    }

    /**
     * Paste image from clipboard
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function paste()
    {
        $input = file_get_contents("php://input");

        $validator = $this->getValidationFactory()->make(
            ['length' => strlen($input)],
            ['length' => 'max:' . config('filesystems.upload_max_size') * 1024 * 1024]
        );

        $this->validateWith($validator);

        $media = $this->getMediaFactory('attachment')->put($input);
        $mime = new Mimetypes();

        $data = [
            'size' => $media->size(),
            'mime' => $mime->fromFilename($media->path()),
            'file' => $media->getFilename(),
            'name' => $media->getName()
        ];

        $attachment = $this->attachment->create($data);
        return $this->renderForm($attachment);
    }

    /**
     * Download the attachment (or show image)
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response
     */
    public function download($id)
    {
        /** @var \Coyote\Post\Attachment $attachment */
        $attachment = $this->attachment->findOrFail($id);
        $post = $this->post->findOrNew($attachment->post_id, ['id', 'forum_id']);
        $forum = $this->forum->find($post->forum_id);

        if (!$forum->userCanAccess($this->userId)) {
            abort(401, 'Unauthorized');
        }

        set_time_limit(0);

        $attachment->count = $attachment->count + 1;
        $attachment->save();

        $isImage = $attachment->file->isImage();

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
            return response()->make(
                $attachment->file->get(),
                200,
                $headers
            );
        } else {
            return response()->download(
                $attachment->file->path(),
                $attachment->name,
                $headers
            );
        }
    }

    /**
     * @param $data
     * @return string
     */
    protected function renderForm($data)
    {
        $form = $this->createForm(AttachmentForm::class, $data);
        // we're changing field name because front end expect this field to be an array
        $form->get('file')->setName('attachments[][file]');

        return $form->render();
    }
}
