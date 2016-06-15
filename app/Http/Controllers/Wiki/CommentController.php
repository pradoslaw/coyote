<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\Wiki\CommentForm;
use Coyote\Services\Stream\Objects\Wiki as Stream_Wiki;
use Coyote\Services\Stream\Objects\Comment as Stream_Comment;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;

class CommentController extends Controller
{
    /**
     * @param \Coyote\Wiki $wiki
     * @param int|null $id
     * @return \Illuminate\View\View
     */
    public function save($wiki, $id = null)
    {
        /** @var \Coyote\Wiki\Comment $comment */
        $comment = $wiki->comments()->findOrNew($id);

        $form = $this->createForm(CommentForm::class, $comment);
        $form->validate();

        $comment->fill($form->all());

        if (!$comment->exists) {
            $comment->user_id = $this->userId;
            $comment->ip = $form->getRequest()->ip();
        }

        $this->transaction(function () use ($wiki, $comment) {
            $comment->save();

            stream(
                $comment->wasRecentlyCreated ? Stream_Create::class : Stream_Update::class,
                (new Stream_Comment())->map($wiki, $comment),
                (new Stream_Wiki())->map($wiki)
            );
        });

        $parser = app('parser.comment');

        $comment->original_text = $comment->text;
        $comment->text = $parser->parse($comment->text);

        return view('wiki.partials.comment', ['comment' => $comment, 'wiki' => $wiki, 'form' => $form]);
    }
}
