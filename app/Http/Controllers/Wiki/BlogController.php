<?php

namespace Coyote\Http\Controllers\Wiki;

class BlogController extends BaseController
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Blog', route('wiki.blog'));

        /** @var \Coyote\Wiki $blog */
        $blog = $this->wiki->findByPath('Blog');

        $children = $this->wiki->where('parent_id', $blog->id)->orderBy('created_at', 'DESC')->paginate(5);
        $parser = $this->getParser();

        foreach ($children as &$row) {
            /** @var \Coyote\Wiki $row */
            $row->text = $parser->parse($row->text);
        }

        return $this->view('wiki.blog.home', [
            'children' => $children,
            'wiki' => $blog
        ]);
    }
}
