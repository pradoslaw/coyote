<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Http\Forms\Wiki\CommentForm;
use Illuminate\Http\Request;

class ShowController extends BaseController
{
    public function index(Request $request)
    {
        /** @var \Coyote\Wiki $wiki */
        $wiki  = $request->wiki;

        $author = $wiki->logs()->first()->user;
        $wiki->text = $this->getParser()->parse((string) $wiki->text);

        $parser = app('parser.comment');
        $wiki->load('comments.user');

        foreach ($wiki->comments as &$comment) {
            /** @var \Coyote\Wiki\Comment $comment */
            $comment->text = $parser->parse($comment->text);
        }

        return $this->view('wiki.' . $wiki->template, [
            'wiki' => $wiki,
            'author' => $author,
            'categories' => $this->wiki->getAllCategories($wiki->id),
            'parents' => $this->parents->slice(1)->reverse(), // we skip current page
            'form' => $this->createForm(CommentForm::class, [], [
                'url' => route('wiki.comment.save', [$wiki->id])
            ])
        ]);
    }
}
