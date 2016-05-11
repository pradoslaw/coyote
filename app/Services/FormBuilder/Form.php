<?php

namespace Coyote\Services\FormBuilder;

abstract class Form extends FormRequest implements FormInterface
{
    use CreateFieldTrait, RenderTrait;

    const GET = 'GET';
    const POST = 'POST';

    const THEME_INLINE = 'forms.themes.inline';
    const THEME_HORIZONTAL = 'forms.themes.horizontal';

    /**
     * @experimental
     */
    const PRE_RENDER = 'form.pre_render';

    /**
     * @var array
     */
    protected $events = [];

    /**
     * It's public so we can use use attr from twig
     *
     * @var array
     */
    public $attr = [
        'method' => self::POST,
        'accept-charset' => 'UTF-8'
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
     * @var mixed|array
     */
    protected $data;

    /**
     * @return mixed
     */
    abstract public function buildForm();

    /**
     * @inheritdoc
     */
    public function rebuildForm()
    {
        $this->fields = [];
        $this->buildForm();
    }

    /**
     * @param string $name
     * @param \Closure $listener
     * @return $this
     */
    public function addEventListener($name, \Closure $listener)
    {
        $this->events[] = ['name' => $name, 'listener' => $listener];

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function add($name, $type, array $options = [])
    {
        $this->fields[$name] = $this->makeField($name, $type, $this, $options);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMethod()
    {
        return $this->attr['method'];
    }

    /**
     * @inheritdoc
     */
    public function setMethod($method)
    {
        $this->attr['method'] = $method;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->attr['action'];
    }

    /**
     * @inheritdoc
     */
    public function setUrl($url)
    {
        $this->attr['action'] = $url;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAttr()
    {
        return $this->attr;
    }

    /**
     * @inheritdoc
     */
    public function setAttr($attr)
    {
        // user want's to set attributes BUT maybe he does not want to override url attribute?
        $only = array_only($this->attr, ['action', 'method']);

        $this->attr = array_merge($only, $attr);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function setData($data, $rebuildForm = true)
    {
        $this->data = $data;

        if ($rebuildForm && !empty($this->fields)) {
            $this->rebuildForm();
        }

        return $this;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function errors()
    {
        return $this->request->session()->get('errors');
    }

    /**
     * @inheritdoc
     */
    public function getField($field)
    {
        return $this->fields[$field] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function get($field)
    {
        return $this->getField($field);
    }

    /**
     * @inheritdoc
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        $this->fireEvents(self::PRE_RENDER);

        return $this->view($this->getViewPath($this->getTemplate()), [
            'form' => $this,
            'fields' => $this->fields
        ])->render();
    }

    /**
     * @inheritdoc
     */
    public function renderForm()
    {
        $this->fireEvents(self::PRE_RENDER);

        return $this->view($this->getWidgetPath(), ['form' => $this])->render();
    }

    /**
     * @inheritdoc
     */
    public function all()
    {
        return $this->request->all();
    }

    /**
     * @inheritdoc
     */
    public function isSubmitted()
    {
        // @todo obsluga dla metody GET
        return $this->request->method() === $this->getMethod();
    }

    /**
     * @return string
     */
    protected function getWidgetName()
    {
        return 'form_widget';
    }

    /**
     * @param string $name
     */
    protected function fireEvents($name)
    {
        foreach ($this->events as $idx => $event) {
            if ($name === $event['name']) {
                $event['listener']($this);
                unset($this->events[$idx]);
            }
        }
    }

    /**
     * Set up the validation rules
     */
    protected function setupRules()
    {
        $this->rules = (new Rules($this))->getRules();
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed|null
     */
    public function __call($name, $arguments)
    {
        return $this->getField($name);
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function __get($key)
    {
        return $this->getField($key);
    }
}
