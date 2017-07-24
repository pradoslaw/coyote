<?php

namespace Coyote\Http\Validators;

use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;
use Coyote\Wiki;
use Illuminate\Routing\Router;

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
     * @return bool
     */
    public function validateUnique($attribute, $value, $parameters)
    {
        $wikiId = (int) ($parameters[0] ?? null);
        $parentId = ($parameters[1] ?? null);

        $wiki = $this
            ->wiki
            ->whereRaw('LOWER(slug) = ?', [mb_strtolower(Wiki::slug($value))])
            ->where('parent_id', $parentId)
            ->get(['id']);

        if ($wiki->count() > 0 && $wiki->first()->id !== $wikiId) {
            return false;
        }

        return true;
    }

    /**
     * @param mixed $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateRoute($attribute, $value, $parameters)
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

            // ugly hack. skip route with "path" variable because this rule catch all pages.
            if ($compiled && !in_array('path', $compiled->getVariables())) {
                $regexs[] = $compiled->getRegex();
            } else {
                $regexs[] = $this->buildRegex($route->uri());
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
