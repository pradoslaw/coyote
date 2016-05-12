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

        $content = <<<EOF
<div class="row max-width">
    <div class="col-md-2 col-sm-4">
        <ul>
            <li><h6>Programowanie</h6></li>
            <li><a title="Programowanie w Delphi. Kurs programowania oraz encyklopedia biblioteki VCL" href="/Delphi">Delphi</a></li>
            <li><a title="Zagadnienia z dziedziny programowania w C#" href="/C_sharp">C#</a></li>
            <li><a title="Programowanie w językach C i C++" href="/C">C++</a></li>
            <li><a title="Programowanie Java" href="/Java">Java</a></li>
            <li><a title="Wszystko o XHTML oraz HTML" href="/(X)HTML">(X)HTML</a></li>
        </ul>
    </div>

    <div class="col-md-2 col-sm-4">
        <ul>
            <li><h6><a title="Forum dla programistów" href="/Forum">Forum dyskusyjne</a></h6></li>
            <li><a title="Forum dla początkujących programistów" href="/Forum/Newbie">Newbie</a></li>
            <li><a title="Forum dla programistów Javy" href="/Forum/Java">Java</a></li>
            <li><a title="Programowanie w C oraz C++" href="/Forum/C_i_C++">C/C++</a></li>
            <li><a title="Kariera programisty" href="/Forum/Kariera">Kariera</a></li>
            <li><a title="Praca dla programisty" href="/Forum/Edukacja">Edukacja</a></li>

        </ul>
    </div>

    <div class="col-md-2 col-sm-4">
        <ul>
            <li><h6>Sprawy administracyjne</h6></li>
            <li><a title="Jeżeli znalazłeś błąd w oprogramowaniu, tu jest odpowiednie miejsce, aby to zgłosić" href="/Forum/Coyote">Zgłoś błąd w oprogramowaniu</a></li>
            <li><a title="4programmers.net na facebooku" href="https://www.facebook.com/4programmers.net">4programmers.net na facebooku</a></li>
            <li><a title="Blog 4programmers.net" href="/Blog">Blog 4programmers.net</a></li>
            <li><a title="Prawa autorskie" href="/Prawa_autorskie">Prawa autorskie</a></li>
            <li><a title="Logo 4programmers.net" href="/Pomoc/Nasze_logo">Nasze logo</a></li>
        </ul>
    </div>

    <div class="col-md-2 col-sm-4">
        <ul>
            <li><h6>O nas</h6></li>
            <li><a title="Kontakt z serwisem 4programmers.net" href="/Kontakt">Kontakt</a></li>
            <li><a title="Regulamin korzystania z serwisu 4programmers.net" href="/Regulamin">Regulamin</a></li>
            <li><a title="Zareklamuj się w serwisie 4programmers.net" href="/Reklama">Reklama</a></li>
            <li><a title="Pomoc w korzystaniu z serwisu 4programmers.net" href="/Pomoc">Pomoc</a></li>
            <li><a title="Odpowiedzi na najczęściej zadawane pytania" href="/Patronat">Patronat</a></li>
        </ul>
    </div>

    <div class=" col-sm-4">
        <div class="footer-bubble">
            <h6>Skontaktuj się z nami</h6>

            <ol>
                <li><a title="Skontaktuj się poprzez email" href="/Kontakt"><i class="fa fa-info-circle fa-fw"></i>  Napisz do nas</a></li>
                <li><a title="Skontaktuj się poprzez email" href="https://www.facebook.com/4programmers.net"><i class="fa fa-facebook fa-fw"></i> Odwiedź nas na Facebooku</a></li>
            </ol>
        </div>
    </div>
</div>
EOF;

        \Coyote\Block::create([
            'name' => 'footer',
            'content' => $content
        ]);
    }
}
