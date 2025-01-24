<?php
namespace Coyote\Providers;

use Coyote\Repositories\Contracts\ActivityRepositoryInterface;
use Coyote\Repositories\Contracts\BlockRepositoryInterface;
use Coyote\Repositories\Contracts\CampaignRepositoryInterface;
use Coyote\Repositories\Contracts\CountryRepositoryInterface;
use Coyote\Repositories\Contracts\CouponRepositoryInterface;
use Coyote\Repositories\Contracts\CriteriaInterface;
use Coyote\Repositories\Contracts\CurrencyRepositoryInterface;
use Coyote\Repositories\Contracts\FirewallRepositoryInterface;
use Coyote\Repositories\Contracts\FirmRepositoryInterface;
use Coyote\Repositories\Contracts\FlagRepositoryInterface;
use Coyote\Repositories\Contracts\ForumRepositoryInterface;
use Coyote\Repositories\Contracts\GroupRepositoryInterface;
use Coyote\Repositories\Contracts\GuestRepositoryInterface;
use Coyote\Repositories\Contracts\GuideRepositoryInterface;
use Coyote\Repositories\Contracts\InvoiceRepositoryInterface;
use Coyote\Repositories\Contracts\JobRepositoryInterface;
use Coyote\Repositories\Contracts\MailRepositoryInterface;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface;
use Coyote\Repositories\Contracts\NotificationRepositoryInterface;
use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Coyote\Repositories\Contracts\PastebinRepositoryInterface;
use Coyote\Repositories\Contracts\PaymentRepositoryInterface;
use Coyote\Repositories\Contracts\PlanRepositoryInterface;
use Coyote\Repositories\Contracts\PmRepositoryInterface;
use Coyote\Repositories\Contracts\PollRepositoryInterface;
use Coyote\Repositories\Contracts\PostRepositoryInterface;
use Coyote\Repositories\Contracts\RepositoryInterface;
use Coyote\Repositories\Contracts\StreamRepositoryInterface;
use Coyote\Repositories\Contracts\SubscribableInterface;
use Coyote\Repositories\Contracts\TagRepositoryInterface;
use Coyote\Repositories\Contracts\TopicRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Contracts\WikiRepositoryInterface;
use Coyote\Repositories\Contracts\WordRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    protected bool $defer = true;

    public function provides(): array
    {
        return [
            ActivityRepositoryInterface::class,
            BlockRepositoryInterface::class,
            CampaignRepositoryInterface::class,
            CountryRepositoryInterface::class,
            CouponRepositoryInterface::class,
            CriteriaInterface::class,
            CurrencyRepositoryInterface::class,
            FirewallRepositoryInterface::class,
            FirmRepositoryInterface::class,
            FlagRepositoryInterface::class,
            ForumRepositoryInterface::class,
            GroupRepositoryInterface::class,
            GuestRepositoryInterface::class,
            GuideRepositoryInterface::class,
            InvoiceRepositoryInterface::class,
            JobRepositoryInterface::class,
            MailRepositoryInterface::class,
            MicroblogRepositoryInterface::class,
            NotificationRepositoryInterface::class,
            PageRepositoryInterface::class,
            PastebinRepositoryInterface::class,
            PaymentRepositoryInterface::class,
            PlanRepositoryInterface::class,
            PmRepositoryInterface::class,
            PollRepositoryInterface::class,
            PostRepositoryInterface::class,
            RepositoryInterface::class,
            StreamRepositoryInterface::class,
            SubscribableInterface::class,
            TagRepositoryInterface::class,
            TopicRepositoryInterface::class,
            UserRepositoryInterface::class,
            WikiRepositoryInterface::class,
            WordRepositoryInterface::class,
        ];
    }

    public function register(): void
    {
        $this->app->singleton(ActivityRepositoryInterface::class, \Coyote\Repositories\Eloquent\ActivityRepository::class);
        $this->app->singleton(BlockRepositoryInterface::class, \Coyote\Repositories\Eloquent\BlockRepository::class);
        $this->app->singleton(CampaignRepositoryInterface::class, \Coyote\Repositories\Eloquent\CampaignRepository::class);
        $this->app->singleton(CountryRepositoryInterface::class, \Coyote\Repositories\Eloquent\CountryRepository::class);
        $this->app->singleton(CouponRepositoryInterface::class, \Coyote\Repositories\Eloquent\CouponRepository::class);
        $this->app->singleton(CurrencyRepositoryInterface::class, \Coyote\Repositories\Eloquent\CurrencyRepository::class);
        $this->app->singleton(FirewallRepositoryInterface::class, \Coyote\Repositories\Eloquent\FirewallRepository::class);
        $this->app->singleton(FirmRepositoryInterface::class, \Coyote\Repositories\Eloquent\FirmRepository::class);
        $this->app->singleton(FlagRepositoryInterface::class, \Coyote\Repositories\Eloquent\FlagRepository::class);
        $this->app->singleton(ForumRepositoryInterface::class, \Coyote\Repositories\Eloquent\ForumRepository::class);
        $this->app->singleton(GroupRepositoryInterface::class, \Coyote\Repositories\Eloquent\GroupRepository::class);
        $this->app->singleton(GuestRepositoryInterface::class, \Coyote\Repositories\Eloquent\GuestRepository::class);
        $this->app->singleton(GuideRepositoryInterface::class, \Coyote\Repositories\Eloquent\GuideRepository::class);
        $this->app->singleton(InvoiceRepositoryInterface::class, \Coyote\Repositories\Eloquent\InvoiceRepository::class);
        $this->app->singleton(JobRepositoryInterface::class, \Coyote\Repositories\Eloquent\JobRepository::class);
        $this->app->singleton(MailRepositoryInterface::class, \Coyote\Repositories\Eloquent\MailRepository::class);
        $this->app->singleton(MicroblogRepositoryInterface::class, \Coyote\Repositories\Eloquent\MicroblogRepository::class);
        $this->app->singleton(NotificationRepositoryInterface::class, \Coyote\Repositories\Eloquent\NotificationRepository::class);
        $this->app->singleton(PageRepositoryInterface::class, \Coyote\Repositories\Eloquent\PageRepository::class);
        $this->app->singleton(PastebinRepositoryInterface::class, \Coyote\Repositories\Eloquent\PastebinRepository::class);
        $this->app->singleton(PaymentRepositoryInterface::class, \Coyote\Repositories\Eloquent\PaymentRepository::class);
        $this->app->singleton(PlanRepositoryInterface::class, \Coyote\Repositories\Eloquent\PlanRepository::class);
        $this->app->singleton(PmRepositoryInterface::class, \Coyote\Repositories\Eloquent\PmRepository::class);
        $this->app->singleton(PollRepositoryInterface::class, \Coyote\Repositories\Eloquent\PollRepository::class);
        $this->app->singleton(PostRepositoryInterface::class, \Coyote\Repositories\Eloquent\PostRepository::class);
        $this->app->singleton(RepositoryInterface::class, \Coyote\Repositories\Eloquent\Repository::class);
        $this->app->singleton(StreamRepositoryInterface::class, \Coyote\Repositories\Eloquent\StreamRepository::class);
        $this->app->singleton(TagRepositoryInterface::class, \Coyote\Repositories\Eloquent\TagRepository::class);
        $this->app->singleton(TopicRepositoryInterface::class, \Coyote\Repositories\Eloquent\TopicRepository::class);
        $this->app->singleton(UserRepositoryInterface::class, \Coyote\Repositories\Eloquent\UserRepository::class);
        $this->app->singleton(WikiRepositoryInterface::class, \Coyote\Repositories\Eloquent\WikiRepository::class);
        $this->app->singleton(WordRepositoryInterface::class, \Coyote\Repositories\Eloquent\WordRepository::class);
    }
}
