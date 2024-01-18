<?php
namespace Tests\Unit\Seo\DiscussionForumPosting;

use Illuminate\Support\Facades\DB;
use Tests\Unit\BaseFixture\Server\Laravel;

trait SystemDatabase
{
    use Laravel\Application, Laravel\Transactional;

    function systemDatabaseTimezone(string $timezone): void
    {
        DB::statement("SET TIMEZONE TO '$timezone'");
    }
}
