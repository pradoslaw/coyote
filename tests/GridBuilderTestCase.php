<?php

use Orchestra\Testbench\TestCase;
use Collective\Html\HtmlBuilder;
use Collective\Html\FormBuilder;
use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Boduch\Grid\GridHelper;

abstract class GridBuilderTestCase extends TestCase
{
    /**
     * @var ViewFactory
     */
    protected $view;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var GridHelper
     */
    protected $gridHelper;

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

    public function setUp(): void
    {
        parent::setUp();

        $this->view = $this->app['view'];
        $this->request = $this->app['request'];
        $this->request->setLaravelSession($this->app['session.store']);
        $this->validator = $this->app['validator'];
        $this->htmlBuilder = new HtmlBuilder($this->app['url'], $this->view);
        $this->formBuilder = new FormBuilder($this->htmlBuilder, $this->app['url'], $this->view, $this->request->session()->token());

        $this->gridHelper = new GridHelper($this->request, $this->validator, $this->view, $this->htmlBuilder, $this->formBuilder);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
