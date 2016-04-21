<?php

namespace Coyote\Services\FormBuilder;

use Illuminate\Foundation\Http\FormRequest;

abstract class Form extends FormRequest
{
    const GET = 'GET';
    const POST = 'POST';

    const THEME_INLINE = 'forms.themes.inline';
    const THEME_HORIZONTAL = 'forms.themes.horizontal';

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
        return $this->session()->get('errors');
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
     * Get the validator instance for the request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        if ($this->isSubmitted()) {
            if (empty($this->fields)) {
                $this->buildForm();
            }

            $this->setupRules();
        }

        // apply validator rules
        return parent::getValidatorInstance();
    }

    /**
     * @return bool
     */
    public function isSubmitted()
    {
        // @todo obsluga dla metody GET
        return $this->method() === $this->getFormMethod();
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

    public function __get($key)
    {
        // UWAGA! Dzialanie tej magic method jest dwojakie. Albo zwraca element formularza
        // albo nastepuje o dwolanie do klasy macierzystej, ktora zwraca wartosc pola formularza
        // Mamy tutaj dwie dwojakie dzialanie. Troche slabe...
        if (isset($this->fields[$key])) {
            return $this->fields[$key];
        }

        return parent::__get($key);
    }
}
