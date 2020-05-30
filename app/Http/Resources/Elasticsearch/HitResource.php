<?php

namespace Coyote\Http\Resources\Elasticsearch;

use Coyote\Job;
use Coyote\Microblog;
use Coyote\Services\Breadcrumb;
use Coyote\Topic;
use Coyote\User;
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
        $result = array_except($this->resource, ['forum']) + ['breadcrumbs' => $this->breadcrumb()];

        if (!empty($this->resource['children']) && !$this->hasHighlights($this->resource['text'] ?? null)) {
            $child = array_shift($result['children']);

            $result = array_merge($result, $child); // move highlighted text to parent entry
        }

        return $result;
    }

    private function hasHighlights(?string $text): bool
    {
        if (!$text) {
            return false;
        }

        return str_contains($text, '<em>');
    }

    private function breadcrumb()
    {
        $baseName = class_basename($this->resource['model']);

        $models = [class_basename(Topic::class), class_basename(Job::class), class_basename(Wiki::class), class_basename(Microblog::class), class_basename(User::class)];
        $name = array_combine($models, ['Forum', 'Praca', 'Kompendium', 'Mikroblog', 'Użytkownicy']);
        $routes = array_combine($models, [route('forum.home'), route('job.home'), url('Kompendium'), route('microblog.home'), 'javascript:']);

        $breadcrumb = new Breadcrumb();
        $breadcrumb->push($name[$baseName], $routes[$baseName]);

        if (!empty($this->resource['forum'])) {
            $breadcrumb->push($this->resource['forum']['name'], $this->resource['forum']['url']);
        }

        return $breadcrumb->toArray();
    }
}
