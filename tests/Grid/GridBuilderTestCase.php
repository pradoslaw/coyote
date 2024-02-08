<?php
namespace Tests\Grid;

use Boduch\Grid\GridHelper;
use Collective\Html\FormBuilder;
use Collective\Html\HtmlBuilder;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Server\Laravel;

abstract class GridBuilderTestCase extends TestCase
{
    use Laravel\Application;
    
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

        $this->view = $this->laravel->app['view'];
        $this->request = $this->laravel->app['request'];
        $this->request->setLaravelSession($this->laravel->app['session.store']);
        $this->validator = $this->laravel->app['validator'];
        $this->htmlBuilder = new HtmlBuilder($this->laravel->app['url'], $this->view);
        $this->formBuilder = new FormBuilder($this->htmlBuilder, $this->laravel->app['url'], $this->view, $this->request->session()->token());

        $this->gridHelper = new GridHelper($this->request, $this->validator, $this->view, $this->htmlBuilder, $this->formBuilder);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
