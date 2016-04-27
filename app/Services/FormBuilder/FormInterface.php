<?php

namespace Coyote\Services\FormBuilder;

use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Container\Container;

interface FormInterface
{
    /**
     * @return mixed
     */
    public function buildForm();

    /**
     * Remove existing fields and build them again.
     */
    public function rebuildForm();

    /**
     * @param string $name
     * @param string $type
     * @param array $options
     * @return $this
     */
    public function add($name, $type, array $options = []);

    /**
     * @return mixed
     */
    public function getMethod();

    /**
     * @param $method
     * @return $this
     */
    public function setMethod($method);

    /**
     * @return mixed
     */
    public function getUrl();

    /**
     * @param mixed $url
     * @return $this
     */
    public function setUrl($url);

    /**
     * @return array
     */
    public function getAttr();

    /**
     * @param array $attr
     * @return $this
     */
    public function setAttr($attr);

    /**
     * @return array|mixed
     */
    public function getData();

    /**
     * @param array|mixed $data
     * @param bool $rebuildForm
     * @return $this
     */
    public function setData($data, $rebuildForm = true);

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options = []);

    /**
     * @return mixed
     */
    public function errors();

    /**
     * @param $field
     * @return mixed|null
     */
    public function getField($field);

    /**
     * @param $field
     * @return mixed|null
     */
    public function get($field);

    /**
     * @return array
     */
    public function getFields();

    /**
     * Render entire form
     *
     * @return string
     */
    public function render();

    /**
     * Render only opening tag
     *
     * @return string
     */
    public function renderForm();

    /**
     * @return array
     */
    public function all();

    /**
     * @return bool
     */
    public function isSubmitted();

    /**
     * @return bool
     */
    public function authorize();

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request);

    /**
     * @return Request
     */
    public function getRequest();

    /**
     * Set the Redirector instance.
     *
     * @param  \Illuminate\Routing\Redirector  $redirector
     * @return Form
     */
    public function setRedirector(Redirector $redirector);

    /**
     * Set the container implementation.
     *
     * @param  \Illuminate\Container\Container  $container
     * @return $this
     */
    public function setContainer(Container $container);

    /**
     * @return Container
     */
    public function getContainer();

    /**
     * @return boolean
     */
    public function isValidationEnabled();

    /**
     * @param bool $flag
     * @return $this
     */
    public function setEnableValidation($flag);
}
