<?php
namespace Tests\Legacy\IntegrationNew\Seo\Schema\DiscussionForumPosting\Fixture;

use Illuminate\Support\Facades\DB;
use Tests\Legacy\IntegrationNew\BaseFixture\Server\Laravel;

trait SystemDatabase
{
    use Laravel\Transactional;

    function systemDatabaseTimezone(string $timezone): void
    {
        DB::statement("SET TIMEZONE TO '$timezone'");
    }
}
