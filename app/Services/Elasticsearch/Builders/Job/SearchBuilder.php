<?php

namespace Coyote\Services\Elasticsearch\Builders\Job;

use Coyote\Services\Elasticsearch\Aggs;
use Coyote\Services\Elasticsearch\Query;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;
use Coyote\Services\Elasticsearch\Filters;
use Coyote\Services\Elasticsearch\Sort;
use Coyote\Services\Parser\Helpers\City;
use Illuminate\Http\Request;

class SearchBuilder
{
    const PER_PAGE = 15;
    const DEFAULT_SORT = '_score';

    /**
     * @var Request
     */
    private $request;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var Filters\Job\City
     */
    public $city;

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
    }

    /**
     * @param mixed $preferences
     */
    public function setPreferences($preferences)
    {
        if (!empty($preferences->city)) {
            $this->city->setCities((new City())->grab($preferences->city));
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
                new Query($this->request->get('q'), ['title', 'description', 'requirements', 'recruitment', 'tags'])
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
        $this->queryBuilder->addSort(new Sort($sort, $this->getOrder()));

        // @todo jezeli sortujemy po "trafnosci" w sytuacji gdy uzytkownik chce wyswietlic wszystkie wyniki
        // mozemy dodac sortowanie po "jakosci" ogloszenia. w ten sposob te lepsze ogloszenia beda na liscie
        // nieco wyzej niz te gorsze. w ten sposob ogloszenia nie beda posortowane po dacie. nalezy sie zastanowic
        // czy takie dzialanie jest pozadane?
        if ($sort === '_score') {
            $this->queryBuilder->addSort(new Sort('rank', 'desc'));
        }

        // it's really important. we MUST show only active offers
        $this->queryBuilder->addFilter(new Filters\Range('deadline_at', ['gte' => 'now']));
        $this->queryBuilder->addFilter($this->city);
        $this->queryBuilder->addFilter($this->tag);

        // facet search
        $this->queryBuilder->addAggs(new Aggs\Job\Location());
        $this->queryBuilder->addAggs(new Aggs\Job\Remote());
        $this->queryBuilder->addAggs(new Aggs\Job\Tag());
        $this->queryBuilder->setSize(self::PER_PAGE * ($this->request->get('page', 1) - 1), self::PER_PAGE);

        return $this->queryBuilder;
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
