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
use Coyote\Repositories\Eloquent\JobRepository;
use Coyote\Services\Elasticsearch\Builders\Job\MoreLikeThisBuilder;
use Coyote\Services\Flags;
use Coyote\Services\Parser\Extensions\Emoji;
use Coyote\Services\UrlBuilder;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function __construct(private JobRepository $job)
    {
        parent::__construct();
    }

    public function index(Job $job): View
    {
        $this->breadcrumb->push('Praca', route('job.home'));
        $this->breadcrumb->push($job->title, UrlBuilder::job($job, true));
        $parser = app('parser.job');
        if (!empty($job->description)) {
            $job->description = $parser->parse($job->description);
        }
        if (!empty($job->requirements)) {
            $job->requirements = $parser->parse($job->requirements);
        }
        if (!empty($job->recruitment)) {
            $job->recruitment = $parser->parse($job->recruitment);
        }
        if ($job->firm_id) {
            $job->firm->description = $parser->parse($job->firm->description ?? '');
        }
        $job->addReferer(url()->previous());
        return $this->view('job.offer', [
            'rates_list'      => Job::getRatesList(),
            'employment_list' => Job::getEmploymentList(),
            'employees_list'  => Firm::getEmployeesList(),
            'seniority_list'  => Job::getSeniorityList(),
            'subscribed'      => $this->userId ? $job->subscribers()->forUser($this->userId)->exists() : false,
            'previous_url'    => $this->request->session()->get('current_url'),
            'payment'         => $this->userId === $job->user_id ? $job->getUnpaidPayment() : null,
            'tags'            => $job->tags()->orderBy('priority', 'DESC')->with('category')->get()->groupCategory(),
            'comments'        => new CommentCollection($job->commentsWithChildren)->setOwner($job)->toArray($this->request),
            'applications'    => $this->applications($job),
            'flags'           => $this->flags(),
            'assets'          => AssetsResource::collection($job->firm->assets)->toArray($this->request),
            'subscriptions'   => $this->subscriptions(),
            'emojis'          => Emoji::all(),
            'job'             => $job,
            'mlt'             => $this->job->search(new MoreLikeThisBuilder($job))->getSource(),
            'is_author'       => $job->enable_apply && $job->user_id === auth()->user()?->id,
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
        /** @var Flags $flags */
        $flags = resolve(Flags::class);
        $resourceFlags = $flags
            ->fromModels([Job::class, Comment::class])
            ->permission('job-delete')
            ->get();
        return FlagResource::collection($resourceFlags)->toArray($this->request);
    }

    private function subscriptions(): array
    {
        return $this->userId ? JobResource::collection($this->job->subscribes($this->userId))->toArray($this->request) : [];
    }
}
