<?php

namespace Boduch\Grid;

use Collective\Html\HtmlBuilder;
use Collective\Html\FormBuilder;
use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\View\Factory as ViewFactory;

class GridHelper
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ViewFactory
     */
    protected $view;

    /**
     * @var ValidationFactory
     */
    protected $validator;

    /**
     * @var HtmlBuilder
     */
    protected $htmlBuilder;

    /**
     * @var FormBuilder
     */
    protected $formBuilder;

    /**
     * @param Request $request
     * @param ValidationFactory $validator
     * @param ViewFactory $view
     * @param HtmlBuilder $htmlBuilder
     * @param FormBuilder $formBuilder
     */
    public function __construct(
        Request $request,
        ValidationFactory $validator,
        ViewFactory $view,
        HtmlBuilder $htmlBuilder,
        FormBuilder $formBuilder
    ) {
        $this->request = $request;
        $this->view = $view;
        $this->validator = $validator;
        $this->htmlBuilder = $htmlBuilder;
        $this->formBuilder = $formBuilder;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return ViewFactory
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @return ValidationFactory
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * @return HtmlBuilder
     */
    public function getHtmlBuilder()
    {
        return $this->htmlBuilder;
    }

    /**
     * @return FormBuilder
     */
    public function getFormBuilder()
    {
        return $this->formBuilder;
    }

    /**
     * @param $rules array
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function getValidatorInstance($rules)
    {
        return $this->validator->make($this->request->all(), $rules);
    }

    /**
     * Generate an html tag.
     *
     * @param string $tag
     * @param string $content
     * @param array  $attributes
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function tag($tag, $content, array $attributes = [])
    {
        return $this->htmlBuilder->tag($tag, $content, $attributes);
    }
}
