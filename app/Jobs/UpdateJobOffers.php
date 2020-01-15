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
        $firm = $firm->find($this->firmId, ['name', 'logo']);

        $params = [
            'index' => config('elasticsearch.default_index'),
            'type' => '_doc'
        ];

        $job->pushCriteria(new PriorDeadline());
        $result = $job->findWhere(['firm_id' => $this->firmId, 'is_publish' => 1], ['id']);

        if (!$result) {
            return;
        }

        foreach ($result as $row) {
            $client->update(array_merge($params, [
                'id' => "job_$row[id]",
                'body' => [
                    'doc' => [
                        'firm' => [
                            'name' => $firm['name'],
                            'logo' => (string) $firm['logo'] // cast to string returns filename
                        ]
                    ]
                ]
            ]));
        }

        $job->resetCriteria();
    }
}
