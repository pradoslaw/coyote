<?php

namespace Coyote\Listeners;

use Coyote\Events\JobDeleting;
use Coyote\Events\JobWasSaved;
use Coyote\Events\PaymentPaid;
use Coyote\Jobs\UpdateJobOffers;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Services\Elasticsearch\Crawler;
use Illuminate\Events\Dispatcher;

// Uwaga! Tutaj specjalnie nie implementujemy interfejsu ShouldQueue poniewaz chcemy zeby usuniecie
// czy dodanie oferty do indeksu nastapilo momentalnie.
class JobListener
{
    /**
     * @var JobRepository
     */
    protected $job;

    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * @param JobRepository $job
     */
    public function __construct(JobRepository $job)
    {
        $this->job = $job;
        $this->crawler = new Crawler();
    }

    /**
     * @param JobWasSaved $event
     */
    public function onJobSave(JobWasSaved $event)
    {
        if ($event->job->is_publish) {
            $this->crawler->index($event->job);
        }

        // we need to update elasticsearch index by updating firm name and logo in all job offers
        if ($event->job->firm_id && $event->job->firm->isDirty(['name', 'logo'])) {
            dispatch_sync(new UpdateJobOffers($event->job->firm_id));
        }
    }

    /**
     * @param JobDeleting $event
     * @throws \Exception
     */
    public function onJobDeleting(JobDeleting $event)
    {
        if ($event->job->is_publish) {
            $this->crawler->delete($event->job);
        }
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(JobWasSaved::class, 'Coyote\Listeners\JobListener@onJobSave');
        $events->listen(JobDeleting::class, 'Coyote\Listeners\JobListener@onJobDeleting');

        $events->listen(PaymentPaid::class, ChangePaymentStatus::class);
        $events->listen(PaymentPaid::class, BoostJobOffer::class);
        $events->listen(PaymentPaid::class, GrantUserPlanBundle::class);
    }
}
