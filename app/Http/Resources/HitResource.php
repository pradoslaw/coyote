<?php

namespace Coyote\Http\Resources;

use Coyote\Job;
use Coyote\Microblog;
use Coyote\Services\Breadcrumb;
use Coyote\Topic;
use Coyote\Wiki;
use Illuminate\Http\Resources\Json\JsonResource;

class HitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $result = array_except($this->resource, ['forum', 'posts']) + ['breadcrumbs' => $this->breadcrumb()];

        if (!empty($this->resource['children']) && empty($this->resource['text'])) {
            $child = array_shift($result['children']);

            $result = array_merge($result, $child);
        }

        return $result;
    }

    private function breadcrumb()
    {
        $baseName = class_basename($this->resource['model']);

        $models = [class_basename(Topic::class), class_basename(Job::class), class_basename(Wiki::class), class_basename(Microblog::class)];
        $name = array_combine($models, ['Forum', 'Praca', 'Kompendium', 'Mikroblog']);
        $routes = array_combine($models, [route('forum.home'), route('job.home'), url('Kompendium'), route('microblog.home')]);

        $breadcrumb = new Breadcrumb();
        $breadcrumb->push($name[$baseName], $routes[$baseName]);

        if (!empty($this->resource['forum'])) {
            $breadcrumb->push($this->resource['forum']['name'], $this->resource['forum']['url']);
        }

        return $breadcrumb->toArray();
    }
}
