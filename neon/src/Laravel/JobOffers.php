<?php
namespace Neon\Laravel;

use Coyote\Job;
use Neon\Domain\Offer;

readonly class JobOffers implements \Neon\Persistence\JobOffers
{
    /**
     * @return Offer[]
     */
    public function fetchJobOffers(): array
    {
        $jobs = Job::query()
            ->orderBy('updated_at', 'DESC') // todo this is not tested
            ->limit(3) // todo this is not tested
            ->get()
            ->all();

        return \array_map(
            function (Job $job) {
                return new Offer(
                    $job->title,
                    $job->firm->name,
                    $job->locations->map(fn(Job\Location $location) => $location->city)->all(),
                    ['Java', 'Spring'],
                    $job->firm->logo->url() ?? '');
            },
            $jobs);
    }
}
