<?php

namespace Coyote\Services\Elasticsearch\Builders\Job;

use Coyote\Services\Elasticsearch\Aggs;
use Coyote\Services\Elasticsearch\Functions\Decay;
use Coyote\Services\Elasticsearch\Functions\FieldValueFactor;
use Coyote\Services\Elasticsearch\MultiMatch;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;
use Coyote\Services\Elasticsearch\Filters;
use Coyote\Services\Elasticsearch\Sort;
use Illuminate\Http\Request;

class SearchBuilder
{
    const PER_PAGE = 15;
    const DEFAULT_SORT = '_score';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var Filters\Job\City
     */
    public $city;

    /**
     * @var Filters\Job\Location
     */
    public $location;

    /**
     * @var Filters\Job\Tag
     */
    public $tag;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->queryBuilder = new QueryBuilder();
        $this->city = new Filters\Job\City();
        $this->tag = new Filters\Job\Tag();
        $this->location = new Filters\Job\Location();
    }

    /**
     * @param \Coyote\Job\Preferences $preferences
     */
    public function setPreferences($preferences)
    {
        if (!empty($preferences->locations)) {
            $this->location->setLocations($preferences->locations);
        }

        if (!empty($preferences->tags)) {
            $this->tag->setTags($preferences->tags);
        }

        if (!empty($preferences->is_remote)) {
            $this->addRemoteFilter();
        }

        if (!empty($preferences->salary)) {
            $this->addSalaryFilter($preferences->salary, $preferences->currency_id);
        }
    }

    /**
     * Apply remote job filter
     */
    public function addRemoteFilter()
    {
        $this->queryBuilder->addFilter(new Filters\Job\Remote());
    }

    /**
     * @param int $salary
     * @param int $currencyId
     */
    public function addSalaryFilter($salary, $currencyId)
    {
        $this->queryBuilder->addFilter(new Filters\Range('salary', ['gte' => $salary]));
        $this->queryBuilder->addFilter(new Filters\Job\Currency($currencyId));
    }

    /**
     * @param string $name
     */
    public function addFirmFilter($name)
    {
        $this->queryBuilder->addFilter(new Filters\Job\Firm($name));
    }

    /**
     * @return QueryBuilderInterface
     */
    public function build() : QueryBuilderInterface
    {
        if ($this->request->has('q')) {
            $this->queryBuilder->addQuery(
                new MultiMatch($this->request->get('q'), ['title^2', 'description', 'requirements', 'recruitment', 'tags^2', 'firm.name'])
            );
        }

        if ($this->request->has('city')) {
            $this->city->addCity($this->request->get('city'));
        }

        if ($this->request->has('tag')) {
            $this->tag->addTag($this->request->get('tag'));
        }

        if ($this->request->has('salary')) {
            $this->addSalaryFilter($this->request->get('salary'), $this->request->get('currency'));
        }

        if ($this->request->has('remote')) {
            $this->addRemoteFilter();
        }

        $sort = $this->getSort();
        $this->queryBuilder->sort(new Sort($sort, $this->getOrder()));

        $this->addFilters();
        $this->addFunctionScore();
        // facet search
        $this->addAggregation();

        $this->queryBuilder->size(self::PER_PAGE * ($this->request->get('page', 1) - 1), self::PER_PAGE);

        return $this->queryBuilder;
    }

    protected function addFilters()
    {
        // it's really important. we MUST show only active offers
        $this->queryBuilder->addFilter(new Filters\Range('deadline_at', ['gte' => 'now']));
        $this->queryBuilder->addFilter($this->city);
        $this->queryBuilder->addFilter($this->tag);
        $this->queryBuilder->addFilter($this->location);
    }

    protected function addFunctionScore()
    {
        // wazniejsze sa te ofery, ktorych pole score jest wyzsze. obliczamy to za pomoca wzoru: log(score * 1)
        $this->queryBuilder->addFunction(new FieldValueFactor('score', 'log', 1));
        // strsze ogloszenia traca na waznosci, glownie po 14d. z kazdym dniem score bedzie malalo o 1/10
        $this->queryBuilder->addFunction(new Decay('created_at', '14d', 0.1));
    }

    protected function addAggregation()
    {
        $this->queryBuilder->addAggs(new Aggs\Job\Location());
        $this->queryBuilder->addAggs(new Aggs\Job\Remote());
        $this->queryBuilder->addAggs(new Aggs\Job\Tag());
    }

    /**
     * @return string
     */
    private function getSort()
    {
        $sort = $this->request->get('sort', '_score');

        return in_array($sort, ['id', '_score', 'salary']) ? $sort : self::DEFAULT_SORT;
    }

    /**
     * @return string
     */
    private function getOrder()
    {
        $order = $this->request->get('order', 'desc');

        return in_array($order, ['asc', 'desc']) ? $order : 'desc';
    }
}
