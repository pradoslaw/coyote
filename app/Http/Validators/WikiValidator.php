<?php

namespace Coyote\Http\Validators;

use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;
use Illuminate\Http\Request;
use Coyote\Wiki;
use Illuminate\Routing\Router;
use Illuminate\Validation\Validator;

class WikiValidator
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var WikiRepository
     */
    protected $wiki;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @param Request $request
     * @param WikiRepository $wiki
     * @param Router $router
     */
    public function __construct(Request $request, WikiRepository $wiki, Router $router)
    {
        $this->request = $request;
        $this->wiki = $wiki;
        $this->router = $router;
    }

    /**
     * @param mixed $attribute
     * @param mixed $value
     * @param array $parameters
     * @param Validator $validator
     * @return bool
     */
    public function validateUnique($attribute, $value, $parameters, $validator)
    {
        $wikiId = (int) $parameters[0] ?? null;
        $wiki = $this->wiki->findWhere(
            ['slug' => Wiki::slug($value), 'parent_id' => $this->request->input('path_id') ?: null],
            ['id']
        );

        if ($wiki->count() > 0 && $wiki->first()->id !== $wikiId) {
            return false;
        }

        return true;
    }

    /**
     * @param mixed $attribute
     * @param mixed $value
     * @param array $parameters
     * @param Validator $validator
     * @return bool
     */
    public function validateRoute($attribute, $value, $parameters, $validator)
    {
        $slug = Wiki::slug($value);
        $path = '/' . $slug;

        if (!empty($this->request->input('path_id'))) {
            $wiki = $this->wiki->findBy('path_id', $this->request->input('path_id'), ['path']);
            $path = '/' . $wiki->path . '/' . $slug;
        }

        $regexs = [];

        /** @var \Illuminate\Routing\Route $route */
        foreach ($this->router->getRoutes()->getRoutes() as $route) {
            $compiled = $route->getCompiled();

            if ($compiled) {
                $regexs[] = $compiled->getRegex();
            } else {
                $regexs[] = $this->buildRegex($route->getUri());
            }
        }

        foreach ($regexs as $regex) {
            if (preg_match($regex, $path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $uri
     * @return string
     */
    private function buildRegex($uri)
    {
        return '#^/' . $uri . '$#s';
    }
}
