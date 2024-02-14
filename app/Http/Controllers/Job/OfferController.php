<?php
namespace Coyote\Http\Controllers\Job;

use Coyote\Comment;
use Coyote\Firm;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Resources\AssetsResource;
use Coyote\Http\Resources\CommentCollection;
use Coyote\Http\Resources\FlagResource;
use Coyote\Http\Resources\JobResource;
use Coyote\Job;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Services\Elasticsearch\Builders\Job\MoreLikeThisBuilder;
use Coyote\Services\Flags;
use Coyote\Services\Parser\Extensions\Emoji;
use Coyote\Services\UrlBuilder;

class OfferController extends Controller
{
    /**
     * @var JobRepository
     */
    private $job;

    /**
     * @param JobRepository $job
     */
    public function __construct(JobRepository $job)
    {
        parent::__construct();

        $this->job = $job;
    }

    /**
     * @param \Coyote\Job $job
     * @return \Illuminate\View\View
     */
    public function index($job)
    {
        $this->breadcrumb->push('Praca', route('job.home'));
        $this->breadcrumb->push($job->title, UrlBuilder::job($job, true));

        $parser = app('parser.job');

        foreach (['description', 'requirements', 'recruitment'] as $name) {
            if (!empty($job->{$name})) {
                $job->{$name} = $parser->parse($job->{$name});
            }
        }

        if ($job->firm_id) {
            $job->firm->description = $parser->parse((string)$job->firm->description);
        }

        $job->addReferer(url()->previous());

        // search related offers
        $mlt = $this->job->search(new MoreLikeThisBuilder($job))->getSource();

        return $this->view('job.offer', [
            'rates_list'      => Job::getRatesList(),
            'employment_list' => Job::getEmploymentList(),
            'employees_list'  => Firm::getEmployeesList(),
            'seniority_list'  => Job::getSeniorityList(),
            'subscribed'      => $this->userId ? $job->subscribers()->forUser($this->userId)->exists() : false,
            'previous_url'    => $this->request->session()->get('current_url'),
            'payment'         => $this->userId === $job->user_id ? $job->getUnpaidPayment() : null,
            // tags along with grouped category
            'tags'            => $job->tags()->orderBy('priority', 'DESC')->with('category')->get()->groupCategory(),
            'comments'        => (new CommentCollection($job->commentsWithChildren))->setOwner($job)->toArray($this->request),
            'applications'    => $this->applications($job),
            'flags'           => $this->flags(),
            'assets'          => AssetsResource::collection($job->firm->assets)->toArray($this->request),
            'subscriptions'   => $this->subscriptions(),
            'emojis'          => Emoji::all(),
        ])->with([
            'job' => $job,
            'mlt' => $mlt,
        ]);
    }

    /**
     * @param Job $job
     * @return array|\Illuminate\Support\Collection
     */
    private function applications(Job $job)
    {
        if ($this->userId !== $job->user_id) {
            return [];
        }

        return $job->applications()->get();
    }

    private function flags()
    {
        $flags = resolve(Flags::class)->fromModels([Job::class, Comment::class])->permission('job-delete')->get();

        return FlagResource::collection($flags)->toArray($this->request);
    }

    private function subscriptions(): array
    {
        return $this->userId ? JobResource::collection($this->job->subscribes($this->userId))->toArray($this->request) : [];
    }
}
