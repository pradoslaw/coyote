<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FeaturesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Coyote\Feature::forceCreate(['name' => 'System kontroli wersji', 'default' => 'Np. Git, SVN']);
        \Coyote\Feature::forceCreate(['name' => 'System zarządzania projektem', 'default' => 'Np. JIRA, Redmine']);
        \Coyote\Feature::forceCreate(['name' => 'Metodologia Agile', 'default' => 'Np. Scrum']);
        \Coyote\Feature::forceCreate(['name' => 'Komunikator firmowy', 'default' => 'Np. Slack, HipChat']);
        \Coyote\Feature::forceCreate(['name' => 'Code review', 'default' => 'Np. Crucible, Gitlab, Github']);
        \Coyote\Feature::forceCreate(['name' => 'Continuous Integration', 'default' => 'Np. Jenkins, Travis CI']);
        \Coyote\Feature::forceCreate(['name' => 'Testy jednostkowe']);
        \Coyote\Feature::forceCreate(['name' => 'Testy integracyjne']);
        \Coyote\Feature::forceCreate(['name' => 'Testerzy w zespole']);
        \Coyote\Feature::forceCreate(['name' => 'System zarządzania wiedzą', 'default' => 'Np. Confluence, Wiki']);
        \Coyote\Feature::forceCreate(['name' => 'Statyczna analiza kodu', 'default' => 'Np. Sonar, Pylint']);
        \Coyote\Feature::forceCreate(['name' => 'Swoboda w wyborze oprogramowania']);
    }
}
