<?php
namespace Neon\Test\Unit\JobOffers;

use Coyote\Firm;
use Coyote\Job;
use Coyote\Plan;
use Coyote\User;
use Neon\Domain\JobOffer;
use Neon\Laravel\JobOffers;
use Neon\Test\BaseFixture;
use PHPUnit\Framework\TestCase;
use Tests\Integration\BaseFixture\Server\Laravel;

class JobOffersLaravelTest extends TestCase
{
    use Laravel\Application;
    use Laravel\Transactional;
    use BaseFixture\PublicImageUrl;

    /**
     * @test
     */
    public function jobTitle(): void
    {
        $offer = $this->jobOffer(['title' => 'foo']);
        $this->assertSame('foo', $offer->title);
    }

    /**
     * @test
     */
    public function jobCompany(): void
    {
        $offer = $this->jobOffer(['company' => 'masterborn']);
        $this->assertSame('masterborn', $offer->company);
    }

    /**
     * @test
     */
    public function jobCity(): void
    {
        $offer = $this->jobOffer(['city' => 'winterfell']);
        $this->assertSame(['Winterfell'], $offer->cities);
    }

    /**
     * @test
     */
    public function jobFirmLogo(): void
    {
        $this->publicImageBaseUrl('/foo');
        $offer = $this->jobOffer(['logo' => 'image.png']);
        $this->assertSame('/foo/image.png', $offer->imageUrl);
    }

    private function jobOffer(array $fields): JobOffer
    {
        $this->createJobOffer($fields);
        $offers = (new JobOffers())->fetchJobOffers();
        return $offers[0];
    }

    private function createJobOffer(array $fields): void
    {
        $plan = new Plan();
        $plan->name = '';
        $plan->save();
        $user = new User();
        $user->name = \uniqid();
        $user->email = '';
        $user->save();
        $firm = new Firm();
        $firm->name = $fields['company'] ?? '';
        $firm->logo = $fields['logo'] ?? '';
        $firm->user_id = $user->id;
        $firm->save();
        $job = new Job();
        $job->title = $fields['title'] ?? '';
        $job->slug = '';
        $job->user_id = $user->id;
        $job->plan_id = $plan->id;
        $job->firm_id = $firm->id;
        $job->is_publish = true;
        $job->save();
        $location = new Job\Location();
        $location->city = $fields['city'] ?? '';
        $location->job_id = $job->id;
        $location->save();
    }
}
