<?php

namespace Coyote\Jobs;

use Coyote\Repositories\Contracts\FirmRepositoryInterface as FirmRepository;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Criteria\Job\PriorDeadline;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateJobOffers extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var int
     */
    protected $firmId;

    /**
     * @param int $firmId
     */
    public function __construct($firmId)
    {
        $this->firmId = $firmId;
    }

    /**
     * @param JobRepository $job
     * @param FirmRepository $firm
     */
    public function handle(JobRepository $job, FirmRepository $firm)
    {
        $client = app('elasticsearch');
        $result = $firm->find($this->firmId, ['name', 'logo']);

        $params = [
            'index' => config('elasticsearch.default_index'),
            'type' => 'jobs'
        ];

        $job->pushCriteria(new PriorDeadline());

        foreach ($job->findAllBy('firm_id', $this->firmId, ['id']) as $row) {
            $client->update(array_merge($params, [
                'id' => $row['id'],
                'body' => [
                    'doc' => [
                        'firm' => [
                            'name' => $result['name'],
                            'logo' => (string) $result['logo'] // cast to string returns filename
                        ]
                    ]
                ]
            ]));
        }
    }
}
