<?php

namespace Coyote\Http\Validators;

use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;
use Coyote\Wiki;
use Illuminate\Routing\Router;
use Illuminate\Validation\Validator;

class WikiValidator
{
    /**
     * @var WikiRepository
     */
    protected $wiki;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @param WikiRepository $wiki
     * @param Router $router
     */
    public function __construct(WikiRepository $wiki, Router $router)
    {
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
        $wikiId = (int) ($parameters[0] ?? null);
        $parentId = ($parameters[1] ?? null);

        $wiki = $this->wiki->findWhere(
            ['slug' => Wiki::slug($value), 'parent_id' => $parentId],
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

        $parentId = (int) ($parameters[0] ?? null);

        if ($parentId) {
            $wiki = $this->wiki->find($parentId, ['path']);
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
