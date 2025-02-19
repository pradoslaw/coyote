<?php
namespace Coyote\Http\Controllers\Job;

use Carbon\Carbon;
use Coyote\Currency;
use Coyote\Domain\RouteVisits;
use Coyote\Events\JobWasSaved;
use Coyote\Firm;
use Coyote\Firm\Benefit;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Requests\Job\JobRequest;
use Coyote\Http\Resources\FirmFormResource;
use Coyote\Http\Resources\JobFormResource;
use Coyote\Job;
use Coyote\Notifications\Job\CreatedNotification;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Eloquent\FirmRepository;
use Coyote\Repositories\Eloquent\PlanRepository;
use Coyote\Services\SubmitJobService;
use Coyote\Services\UrlBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubmitController extends Controller
{
    public function __construct(
        private FirmRepository   $firm,
        private PlanRepository   $plan,
        private SubmitJobService $submitJob)
    {
        parent::__construct();
    }

    public function renew(Job $job, RouteVisits $visits)
    {
        abort_unless($job->is_expired, 404);
        unset($job->id);
        $job->exists = false; // new job offer
        $job->user_id = $this->userId;
        $job->is_publish = $job->is_ads = $job->is_on_top = $job->is_highlight = false; // reset all plan values
        $job->views = 1; // reset views counter
        return $this->index($job, $visits);
    }

    public function index(Job $job, RouteVisits $visits): View|RedirectResponse
    {
        if (!$job->exists) {
            if (!$this->request->has('plan') && !$this->request->has('copy')) {
                return response()->redirectToRoute('job.business');
            }
            $job = $this->submitJob->loadDefaults($job, $this->auth);
            $visits->visit($this->request->path(), Carbon::now()->toDateString());
            if ($this->request->query->has('copy')) {
                $otherJob = Job::query()->find($this->request->get('copy'));
                if ($otherJob) {
                    $fields = [
                        'title', 'description', 'tags', 'is_remote', 'remote_range', 'is_gross',
                        'salary_from', 'salary_to', 'currency_id', 'rate', 'employment',
                        'email', 'phone', 'seniority', 'plan_id', 'enable_apply', 'recruitment',
                        'firm', 'features',
                    ];
                    foreach ($fields as $field) {
                        $job->$field = $otherJob->$field;
                    }
                    $job->locations = $otherJob->locations->toArray();
                } else {
                    return response()->redirectToRoute('job.submit');
                }
            }
        }

        if (!count($job->locations)) {
            // always one empty location
            $job->locations->add(new Job\Location());
        }

        $this->authorize('update', $job);
        $this->breadcrumb($job);
        $this->firm->pushCriteria(new EagerLoading(['benefits', 'assets']));
        return $this->view('job.submit', [
            'job'              => new JobFormResource($job),
            'firms'            => FirmFormResource::collection($this->firm->findAllBy('user_id', $this->userId)),
            'is_plan_ongoing'  => $job->is_publish,
            'plans'            => $this->plan->active()->toJson(),
            'currencies'       => Currency::all(),
            'default_benefits' => Benefit::getBenefitsList(),
            'employees'        => Firm::getEmployeesList(),
        ]);
    }

    public function save(JobRequest $request, Job $job, SubmitJobService $jobService): string
    {
        $job->fill($request->all());
        if ($request->has('firm.name')) {
            $job->firm->fill($request->input('firm'));
            // firm ID is present. user is changing assigned firm
            if ($request->filled('firm.id')) {
                $job->firm->id = $request->input('firm.id');
                $job->firm->exists = true;
                // syncOriginalAttribute() is important if user changes firm
                $job->firm->syncOriginalAttribute('id');
            } else {
                $job->firm->exists = false;
                unset($job->firm->id);
            }
            Firm::creating(function (Firm $model) {
                $model->user_id = $this->userId;
            });
        } else {
            $job->firm()->dissociate();
        }
        $this->transaction(function () use ($job, $request) {
            $this->submitJob->saveRelations($job, $this->auth);
            if ($job->wasRecentlyCreated || !$job->is_publish) {
                $job->payments()->create([
                    'plan_id' => $job->plan_id,
                    'days'    => $job->plan->length,
                ]);
            }
            event(new JobWasSaved($job)); // we don't queue listeners for this event
        });
        if ($job->wasRecentlyCreated) {
            $job->user->notify(new CreatedNotification($job));
        }
        $unpaidPayment = $jobService->getUnpaidPayment($job);
        if ($unpaidPayment) {
            session()->flash('success', 'Oferta została dodana, lecz nie jest jeszcze promowana. Uzupełnij poniższy formularz, aby zakończyć.');
            return route('job.payment', [$unpaidPayment]);
        }
        session()->flash('success', 'Oferta została prawidłowo dodana.');
        return UrlBuilder::job($job);
    }

    private function breadcrumb(Job $job): void
    {
        $this->breadcrumb->push('Praca', route('job.home'));
        if ($job->exists) {
            $this->breadcrumb->push($job->title, route('job.offer', [$job->id, $job->slug]));
            $this->breadcrumb->push('Edycja oferty', route('job.submit'));
        } else {
            $this->breadcrumb->push('Wystaw ofertę pracy', route('job.submit'));
        }
    }
}
