<?php

namespace Coyote\Services\Elasticsearch;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

class Crawler
{
    /**
     * @var Client
     */
    private $client;

    public function __construct()
    {
        $this->client = app('elasticsearch');
    }

    /**
     * @param Model $model
     */
    public function index(Model $model): void
    {
        $resource = $this->makeResource($model);
        $payload = $resource->response()->getData(true);

        $this->client->index(
            array_merge_recursive($this->getDefaultParams($model), ['body' => array_merge($payload, ['model' => class_basename($model)])])
        );

        unset($resource);
    }

    /**
     * @param Model $model
     * @throws \Exception
     */
    public function delete(Model $model): void
    {
        try {
            $this->client->delete($this->getDefaultParams($model));
        } catch (\Exception $exception) {
            if (!$exception instanceof Missing404Exception) {
                throw $exception;
            }
        }
    }

    /**
     * @param Model $model
     * @return array
     */
    protected function getDefaultParams(Model $model): array
    {
        return [
            'index'     => config('elasticsearch.default_index'),
            'type'      => '_doc',
            'id'        => str_singular($model->getTable()) . '_' . $model->getKey()
        ];
    }

    /**
     * @param Model $model
     * @return JsonResource
     *
     * @uses \Coyote\Http\Resources\Elasticsearch\StreamResource
     * @uses \Coyote\Http\Resources\Elasticsearch\TopicResource
     * @uses \Coyote\Http\Resources\Elasticsearch\PostResource
     */
    protected function makeResource(Model $model): JsonResource
    {
        $name = class_basename($model);
        $resource = "Coyote\\Http\\Resources\\Elasticsearch\\{$name}Resource";

        if (!class_exists($resource)) {
            throw new \InvalidArgumentException("$resource does not exist.");
        }

        $resource::withoutWrapping();

        return $resource::make($model);
    }
}
