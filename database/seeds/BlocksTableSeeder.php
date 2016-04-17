<?php

use Illuminate\Database\Seeder;

class BlocksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $content = <<<EOF
<section id="box-job-offers" class="box sidebar-job-offers">
    <h4><a href="%s">Oferty pracy</a></h4>

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="media">
                <div class="media-left">
                    <a href="#">
                        <img class="media-object" src="/img/apple-touch.png" alt="Power Media S.A.">
                    </a>
                </div>
                <div class="media-body">
                    <h5 class="media-heading"><a href="#">Junior Web Developer</a></h5>

                    <p><a href="#" class="employer">Power Media S.A.</a></p>
                    <p class="bottom"><a href="#">Wrocław</a> • do 5000 zł</p>
                </div>
            </div>
            <div class="media">
                <div class="media-left">
                    <a href="#">
                        <img class="media-object" src="/img/apple-touch.png" alt="Power Media S.A.">
                    </a>
                </div>
                <div class="media-body">
                    <h5 class="media-heading"><a href="#">Junior Web Developer</a></h5>

                    <p><a href="#" class="employer">Power Media S.A.</a></p>
                    <p class="bottom"><a href="#">Wrocław</a> • do 5000 zł</p>
                </div>
            </div>

            <div class="media">
                <a href="#" class="text-align">Więcej ofert w pobliżu Wrocław</a>
            </div>
        </div>
    </div>
</section>
EOF;
        \Coyote\Block::create([
            'name' => 'job_ads',
            'content' => sprintf($content, route('job.home', [], false))
        ]);
    }
}
