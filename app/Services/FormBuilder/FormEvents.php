<?php

namespace Coyote\Services\FormBuilder;

class FormEvents
{
    /**
     * @experimental
     */
    const PRE_RENDER = 'form.pre_render';

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var array
     */
    protected $listeners = [];

    public function __construct(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * @param string $event
     * @param \Closure $listener
     * @return $this
     */
    public function addListener($event, \Closure $listener)
    {
        if (empty($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        array_push($this->listeners[$event], $listener);
    }

    /**
     * @param $event
     * @return bool
     */
    public function dispatch($event)
    {
        if (empty($this->listeners[$event])) {
            return false;
        }

        foreach ($this->listeners[$event] as $listener) {
            $listener($this->form);
        }

        return true;
    }
}
