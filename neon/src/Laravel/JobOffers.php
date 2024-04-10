<?php
namespace Neon\Laravel;

use Coyote\Job;
use Neon\Domain\JobOffer;

readonly class JobOffers implements \Neon\Persistence\JobOffers
{
    /**
     * @return JobOffer[]
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
                return new JobOffer(
                    $job->title,
                    route('job.offer', [$job->id, $job->slug]),
                    $job->firm->name,
                    $job->locations
                        ->filter(fn(Job\Location $location): bool => $location->city)
                        ->map(fn(Job\Location $location): string => $location->city)
                        ->all(),
                    $job->is_remote && $job->remote_range === 100,
                    [],
                    $job->firm->logo->url() ?? '');
            },
            $jobs);
    }
}
