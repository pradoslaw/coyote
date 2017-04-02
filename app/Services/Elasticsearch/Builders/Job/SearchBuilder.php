<?php

namespace Coyote\Services\Elasticsearch\Builders\Job;

use Coyote\Services\Elasticsearch\Aggs;
use Coyote\Services\Elasticsearch\ConstantScore;
use Coyote\Services\Elasticsearch\Functions\Decay;
use Coyote\Services\Elasticsearch\Functions\FieldValueFactor;
use Coyote\Services\Elasticsearch\Functions\Random;
use Coyote\Services\Elasticsearch\Functions\ScriptScore;
use Coyote\Services\Elasticsearch\MultiMatch;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\Filters;
use Coyote\Services\Elasticsearch\Sort;
use Coyote\Services\Geocoder\Location;
use Illuminate\Http\Request;

class SearchBuilder extends QueryBuilder
{
    const PER_PAGE = 15;
    const DEFAULT_SORT = '_score';

    /**
     * @var Request
     */
    protected $request;

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
     * @var string|null
     */
    protected $sessionId = null;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->city = new Filters\Job\City();
        $this->tag = new Filters\Job\Tag();
        $this->location = new Filters\Job\Location();
    }

    /**
     * @param string $sessionId
     */
    public function setSessionId(string $sessionId)
    {
        $this->sessionId = $sessionId;
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
     * @param Location|null $location
     */
    public function boostLocation(Location $location = null)
    {
        $this->should(new Filters\Job\LocationScore($location));
    }

    /**
     * Apply remote job filter
     */
    public function addRemoteFilter()
    {
        $this->must(new Filters\Job\Remote());

        if ($this->request->has('remote_range')) {
            $this->must(new Filters\Job\RemoteRange());
        }
    }

    /**
     * @param int $salary
     * @param int $currencyId
     */
    public function addSalaryFilter($salary, $currencyId)
    {
        $this->must(new Filters\Range('salary', ['gte' => $salary]));
        $this->must(new Filters\Job\Currency($currencyId));
    }

    /**
     * @param string $name
     */
    public function addFirmFilter($name)
    {
        $this->must(new Filters\Job\Firm($name));
    }

    /**
     * @return array
     */
    public function build()
    {
        if ($this->request->has('q')) {
            $this->must(
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

        $this->score(new Random($this->sessionId));
        $this->sort(new Sort($this->getSort(), $this->getOrder()));

        $this->setupFilters();
        $this->setupScoreFunctions();
        // facet search
        $this->setupAggregations();

        // premium offers go first
        $this->should(new ConstantScore(new Filters\Term('boost', true), 100));

        $this->size(self::PER_PAGE * intval($this->request->get('page', 1) - 1), self::PER_PAGE);

        return parent::build();
    }

    protected function setupFilters()
    {
        $this->must($this->city);
        $this->must($this->tag);
        $this->must($this->location);
    }

    protected function setupScoreFunctions()
    {
        // wazniejsze sa te ofery, ktorych pole score jest wyzsze. obliczamy to za pomoca wzoru: log(score * 1)
        $this->score(new FieldValueFactor('score', 'log', 1));
        // strsze ogloszenia traca na waznosci, glownie po 14d. z kazdym dniem score bedzie malalo o 1/10
        // za wyjatkiem pierwszych 2h publikacji
        $this->score(new Decay('created_at', '14d', 0.1, '2h'));
        // extra boost for premium offers
        $this->score(new ScriptScore("doc['boost'].value ? 10 : 1"));
    }

    protected function setupAggregations()
    {
        $this->aggs(new Aggs\Job\Location());
        $this->aggs(new Aggs\Job\Remote());
        $this->aggs(new Aggs\Job\Tag());
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
