<?php

namespace Coyote\Services\FormBuilder;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidatesWhenResolvedTrait;
use Illuminate\Routing\Redirector;
use Illuminate\Container\Container;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

abstract class Form implements FormInterface
{
    use ValidatesWhenResolvedTrait;

    const GET = 'GET';
    const POST = 'POST';

    const THEME_INLINE = 'forms.themes.inline';
    const THEME_HORIZONTAL = 'forms.themes.horizontal';

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * It's public so we can use use attr from twig
     *
     * @var array
     */
    public $attr = [
        'method' => self::POST
    ];

    /**
     * @var string
     */
    protected $theme = self::THEME_HORIZONTAL;

    /**
     * @var string
     */
    protected $template = 'form';

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var mixed|array
     */
    protected $data;

    /**
     * The container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * The redirector instance.
     *
     * @var \Illuminate\Routing\Redirector
     */
    protected $redirector;

    /**
     * The URI to redirect to if validation fails.
     *
     * @var string
     */
    protected $redirect;

    /**
     * The route to redirect to if validation fails.
     *
     * @var string
     */
    protected $redirectRoute;

    /**
     * The controller action to redirect to if validation fails.
     *
     * @var string
     */
    protected $redirectAction;

    /**
     * The key to be used for the view error bag.
     *
     * @var string
     */
    protected $errorBag = 'default';

    /**
     * The input keys that should not be flashed on redirect.
     *
     * @var array
     */
    protected $dontFlash = ['password', 'password_confirmation'];

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the Redirector instance.
     *
     * @param  \Illuminate\Routing\Redirector  $redirector
     * @return \Illuminate\Foundation\Http\FormRequest
     */
    public function setRedirector(Redirector $redirector)
    {
        $this->redirector = $redirector;

        return $this;
    }

    /**
     * Set the container implementation.
     *
     * @param  \Illuminate\Container\Container  $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return mixed
     */
    abstract public function buildForm();

    /**
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @param $name
     * @param $type
     * @param array $options
     * @return $this
     */
    public function add($name, $type, array $options = [])
    {
        $this->fields[$name] = $this->makeField($name, $type, $options);
        return $this;
    }

    /**
     * @return string
     */
    public function getFormMethod()
    {
        return $this->attr['method'];
    }

    /**
     * @param string $formMethod
     */
    public function setFormMethod($formMethod)
    {
        $this->attr['method'] = $formMethod;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->attr['url'];
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->attr['url'] = $url;
    }

    /**
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return array
     */
    public function getAttr()
    {
        return $this->attr;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function addAttr($name, $value)
    {
        $this->attr[$name] = $value;
        return $this;
    }

    /**
     * @param array $attr
     * @return $this
     */
    public function setAttr($attr)
    {
        $this->attr = $attr;
        return $this;
    }

    /**
     * @return array|mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array|mixed $data
     * @return $this
     */
    public function setData($data)
    {
        if (!empty($data)) {
            $this->data = $data;
        }

        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options = [])
    {
        foreach ($options as $key => $values) {
            $methodName = 'set' . ucfirst(camel_case($key));

            if (method_exists($this, $methodName)) {
                $this->$methodName($values);
            }
        }

        return $this;
    }

    /**
     * @param $view
     * @param array $data
     * @return mixed
     */
    public function view($view, $data = [])
    {
        return view($this->getTheme() . '.' . $view, $data);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return $this->rules;
    }

    /**
     * @return mixed
     */
    public function errors()
    {
        return $this->request->session()->get('errors');
    }

    /**
     * @param $field
     * @return mixed|null
     */
    public function getField($field)
    {
        return $this->fields[$field] ?? null;
    }

    /**
     * @param $field
     * @return mixed|null
     */
    public function get($field)
    {
        return $this->getField($field);
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Render entire form
     *
     * @return mixed
     */
    public function render()
    {
        return $this->view($this->getTemplate(), [
            'form' => $this,
            'fields' => $this->fields
        ])->render();
    }

    /**
     * Render only opening tag
     *
     * @return mixed
     */
    public function renderForm()
    {
        return $this->view('form_widget', [
            'form' => $this
        ])->render();
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->request->all();
    }

    /**
     * Get the validator instance for the request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        $factory = $this->container->make(ValidationFactory::class);

        if ($this->isSubmitted()) {
            $this->setupRules();
        }

        if (method_exists($this, 'validator')) {
            return $this->container->call([$this, 'validator'], compact('factory'));
        }

        return $factory->make(
            $this->request->all(),
            $this->container->call([$this, 'rules']),
            $this->messages(),
            $this->attributes()
        );
    }

    /**
     * Set custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * Set custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [];
    }

    /**
     * Get the proper failed validation response for the request.
     *
     * @param  array  $errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function response(array $errors)
    {
        if ($this->request->ajax() || $this->request->wantsJson()) {
            return new JsonResponse($errors, 422);
        }

        return $this->redirector->to($this->getRedirectUrl())
            ->withInput($this->request->except($this->dontFlash))
            ->withErrors($errors, $this->errorBag);
    }

    /**
     * Get the response for a forbidden operation.
     *
     * @return \Illuminate\Http\Response
     */
    public function forbiddenResponse()
    {
        return new Response('Forbidden', 403);
    }

    /**
     * @return bool
     */
    public function isSubmitted()
    {
        // @todo obsluga dla metody GET
        return $this->request->method() === $this->getFormMethod();
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exception\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->response(
            $this->formatErrors($validator)
        ));
    }

    /**
     * Determine if the request passes the authorization check.
     *
     * @return bool
     */
    protected function passesAuthorization()
    {
        if (method_exists($this, 'authorize')) {
            return $this->container->call([$this, 'authorize']);
        }

        return false;
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Http\Exception\HttpResponseException
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException($this->forbiddenResponse());
    }

    /**
     * Format the errors from the given Validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return array
     */
    protected function formatErrors(Validator $validator)
    {
        return $validator->getMessageBag()->toArray();
    }

    /**
     * Get the URL to redirect to on a validation error.
     *
     * @return string
     */
    protected function getRedirectUrl()
    {
        $url = $this->redirector->getUrlGenerator();

        if ($this->redirect) {
            return $url->to($this->redirect);
        } elseif ($this->redirectRoute) {
            return $url->route($this->redirectRoute);
        } elseif ($this->redirectAction) {
            return $url->action($this->redirectAction);
        }

        return $url->previous();
    }

    /**
     * Set up the validation rules
     */
    protected function setupRules()
    {
        foreach ($this->fields as $name => $field) {
            $rules = $field->getRules();

            if ($rules) {
                $this->rules[$name] = $rules;
            }
        }
    }

    /**
     * @param $name
     * @param string $type
     * @param array $options
     * @return mixed
     */
    protected function makeField($name, $type = 'text', array $options = [])
    {
        $fieldType = $this->getFieldType($type);
        $options = $this->setupFieldOptions($options);

        return new $fieldType($name, $type, $this, $options);
    }

    /**
     * @param array $options
     * @return array
     */
    protected function setupFieldOptions(array $options)
    {
        $default = ['theme' => $this->getTheme()];

        foreach ($default as $name => $value) {
            if (empty($options[$name])) {
                $options[$name] = $value;
            }
        }

        return $options;
    }

    /**
     * @param $type
     * @return string
     */
    protected function getFieldType($type)
    {
        return __NAMESPACE__ . '\\Fields\\' . ucfirst($type);
    }

    public function __call($name, $arguments)
    {
        return $this->getField($name);
    }

    public function __get($key)
    {
        return $this->getField($key);
    }
}
