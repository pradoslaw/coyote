<?php

namespace Coyote\Services\FormBuilder;

use Illuminate\View\View;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

trait RenderTrait
{
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
     * @param $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Find widget in home  directory and theme directory. This method is being used in views to render widget.
     * It's also being used in Form class to render <form> HTML element (renderForm() method).
     *
     * @return string
     */
    protected function getWidgetPath()
    {
        $result = '';
        $paths = [$this->getTheme(), 'forms.widgets']; // @todo domyslna sciezka dla widgetow przeniesc do konfiga!

        foreach ($paths as $path) {
            $path .= '.' . $this->getWidgetName();

            if (view()->exists($path)) {
                $result = $path;
                break;
            }
        }

        if (!$result) {
            throw new FileNotFoundException(sprintf('Can\'t find widget %s_widget', $this->getType()));
        }

        return $result;
    }

    /**
     * Get full path to the view (with theme name)
     *
     * @param string $view
     * @return string
     */
    protected function getViewPath($view)
    {
        return $this->getTheme() . '.' . $view;
    }

    /**
     * @param string $view
     * @param array $data
     * @return View
     */
    protected function view($view, $data = [])
    {
        if (!view()->exists($view)) {
            throw new FileNotFoundException(
                sprintf('Can\'t find field view %s to render %s element.', $view, class_basename($this))
            );
        }

        return view($view, $data);
    }
}
