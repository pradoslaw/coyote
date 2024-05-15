<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Coyote\Post;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Database\Seeder;

class OldUserSeeder extends Seeder
{
    public function run(): void
    {
        $topic = \factory(Topic::class)->create();
        $user = $this->oldUser();
        $this->seedPosts($user, $topic);
        $this->addLongPost($user, $topic);
    }

    private function seedPosts(User $user, Topic $topic): void
    {
        foreach (range(1, 100) as $_) {
            $this->seedPost($user, $topic);
        }
    }

    private function seedPost(User $user, Topic $topic): void
    {
        $post = \factory(Post::class)->make([
            'forum_id'   => $topic->forum_id,
            'user_id'    => $user->id,
            'created_at' => Carbon::today()->subHours(\rand(0, 365 * 24 * 24)),
        ]);
        $topic->posts()->save($post);
    }

    private function oldUser(): User
    {
        /** @var User $oldUser */
        $oldUser = User::query()->firstOrCreate(['name' => 'olduser'], [
            'email'      => 'olduser@localhost',
            'password'   => bcrypt('olduser'),
            'reputation' => 15000,
        ]);
        return $oldUser;
    }

    private function addLongPost(User $user, Topic $topic): void
    {
        $post = \factory(Post::class)->make([
            'text'       => $this->longPostContent(),
            'forum_id'   => $topic->forum_id,
            'user_id'    => $user->id,
            'created_at' => Carbon::now(),
        ]);
        $topic->posts()->save($post);
    }

    private function longPostContent(): string
    {
        return <<<'longPost'
Co do banów - bardzo mi się podoba system ostrzeżeń, który funkcjonuje na wielu innych forach. Teraz mamy tak, że jak ktoś robi zmieszanie, to moderator musi samodzielnie osądzić, czy już pała została przegięta i wykopujemy (a jeśli tak, to na jak długo) pacjenta, czy tylko zedytować jego wpis, a może coś napisać na PW, żeby się ogarnął. Do tego czasem trzeba przejrzeć historię pacjenta w https://4programmers.net/Forum/Moderatorzy/Kartoteka albo zajrzeć do postów przez tego pacjenta napisanych.

A w przypadku ostrzeżeń - po prostu, widzę niefajne zachowanie i daję żółta kartkę. Jak ich się uzbiera X to automatycznie leci ban - pierwszy np. na dobę, potem licznik ostrzeżeń się zeruje i zaczynamy zbieranie od nowa. Za drugim razem po komplecie ostrzeżeń - ban na tydzień, Trzeci na miesiąc, kolejny bezterminowo (oczywiście - to są tylko przykładowe okresy). Plus możliwość ręcznego banowania jeśli będzie taka potrzeba.

Moim zdaniem to będzie fajne i to z kilku powodów. Po pierwsze - trochę jak punkty karne u kierowców. Jak wiem, że mam już ich 19 i teraz za jakiś drobiazg mogę stracić prawko, to staram się mocniej pilnować. A po drugie - ułatwi to życie moderacji. Zamiast analizować i sie zastanawiać, czy już zasłużył na bana - po prostu daję ostrzeżenie, a potem już magia się dzieje sama.

Jeszcze w temacie analogii do prawa jazdy - uważam, że po jakimś czasie ostrzeżenia powinny się kasować, bo inaczej doszłoby do absurdu, że mam 2 ostrzeżenia sprzed roku i żeby je usunąć zrobię coś głupiego, dostanę bana na dobę i ostrzeżenia się zerują :laugh: 

> Onboarding/ Przewodnik jak działać na forum, jakie są zasady itp. tak aby mieć dokument do którego można odwołać przed banem.

Tak realnie - czy ktoś to będzie czytać? 
Może lepiej zrobić to w postaci filmu na YT. Z jednej strony - odpalić jakiś film, gdzie masz w kilka minut wyjaśnione co i jak działa to fajna opcja, a do tego 4p by weszło na nowy kanał komunikacji ze społeczeństwem. W ogóle - można by było zastanowić się nad tym, o czym już wieeeele razy mówiłem: skupiamy się na kwestiach technicznych, a głownym problemem forum jest coraz mniejsza liczba wartościowych i merytorycznych użytkowników, którzy chcą pisać nie o wojnie na Ukrainie i tym, że PiS to idioci a PO to złodzieje, tylko o sprawach związanych z tematyką forum. Wydaje mi się, że @pradoslaw ma budżet i ludzi, żeby komuś zlecić chociaż w wymiarze kilku godzin tygodniowo prowadzenie FB, jakiegoś YT, żeby 4p zaczęło istnieć w socjalach. Bo co z tego, że będziemy mieli najlepszy silnik forum na świecie, skoro będzie używany przez kilka osób, dyskutujących głownie o tym, jak ten silnik ulepszyć :confused: 

> ograniczyć pracę moderatorów związaną z edytowaniem wątków nowych użytkowników.

Bardziej bym widział tutaj pójście w kierunku @{Tasmanian Devil} i delikatnych sugestii ze strony Coyote, że może jednak byś poprawił swój post, bo brakuje mu czegoś.

> Shadow ban:

Super opcja, popieram całym sercem. Ale moderatorzy powinni widzieć jednak te posty - analogicznie do treści skasowanych

> Łatwe usunięcie całej aktywności użytkownika:

***<u>To jest absolutny numer 1 na liście rzeczy do wdrożenia.</u>***

> Ekspozycja jego czasu czasu i trwania.

Tak miało być, czy ktoś się zakręcił? Moim zdaniem powinno być ```powodu jego udzielenia oraz czasu trwania``` :tongue: 

> najłatwiejsze jest wyklikanie banu stałego, formularz jest niewygodny i wymaga pisanie rzeczy "z palca"

Tak, tutaj by się przydała lista powodów (analogicznie do kasowania/przenoszenia posta czy wątku) plus opcja "inne" jeśli pojawi się niestandardowa przyczyna. Ale i tak 95% (dane z tyłka) banów to trolling, spam, multikonto.

> Aktualnie nie da się zamknąć raportu w usuniętym poście

Chyba, skoro post został skasowany, to raport powinien się automatycznie kasować/obsługiwać/zamykać. Czyli i tak nie ma co dodawać funkcjonalności zamykania raportu do usuniętego posta, bo raport powinien się sam zamknąć w momencie kasowania posta.

Poza tym przypominam, że jakiś czas temu pojawił się pomysł, żeby rozbudować obsługę raportów - nie tylko ich zamykanie, ale opcja akceptacji raportu albo odrzucenia oraz informacja zwrotna dla zgłaszającego, co się stało z jego zgłoszeniem. Były jeszcze inne pomysły, teraz nie pamiętam dokładnie, jedynie sygnalizuję, ze to też było omawiane.

> Brak konsensusu na temat komentarzy:

Tak, jak piszesz - temat był wałkowany, ale nic z tego nie wynikło. Najpierw trzeba w jakiś sposób - albo poprzez głosowanie, albo niech @pradoslaw zadecyduje we własnym zakresie, ustalić w którym kierunku idziemy. I potem się tego trzymać konsekwentnie. Ja wiele razy mówiłem, że dla mnie komentarze to takie drobne wpisy i byłem przeciw robienia z nich mini-postów, dodawania wyszukanego i rozbudowanego formatowania itp. Ale, jeśli decyzja będzie inna od mojej opinii, to oczywiście będę się trzymać tego, co zostanie ustalone i zasad z tym związanych.

> Osobne powiadomienia dla raportów - aktualnie powiadomienia moderatorskie mieszają się z prywatnymi,

**<u>*To jest numer 2 na liście do wdrożenia.*</u>**
Jeszcze bym dodał (to też się gdzieś przewinęło w innym wątku) ponawianie raportów, które nie zostały obsłużone. Zdarza się, że jakiś raport wisi - w momencie, gdy pojawiło się powiadomienie, aktywni moderatorzy olali albo przegapili. Inni nie dostali powiadomienia, temat zaginął w akcji. Tutaj bym był za tym, żeby co np. 30 minut pojawiały się powiadomienia o niezamkniętych raportach (oraz innych rzeczach, wymagających uwagi) -  w ten  sposób nic nie umknie.

> Kartoteka delikwenta:     Aktualnie "kartoteka" jest prowadzona ręcznie po prostu w wątku, jest to prymitywne rozwiązanie które do tej pory działało, ale brakuje mu pewnych cech:

O tym samym myślałem, kiedy pisałem parę akapitów wcześniej o banach i ocenianiu, czy już pacjenta wykopać, czy jeszcze może trochę działać. W obecnej postaci jest to uciążliwe, a jak ktoś był wpisany jakiś czas temu i spadł na dół listy wpisów w Kartotece, raczej nikt go nie namierzy. Tak samo jak np. zmieni nazwę - szukamy kogoś z określonym nickiem, a w kartotece wisi pod starym hasłem i ciężko będzie połapać się, że chodzi o tego samego pacjenta. 

> Zaangażowanie użytkowników w walkę z trollami:

Może jakieś statystyki - że np. masz 90% skuteczności w raportowaniu, albo jesteś na 3 miejscu osób raportujacych? 
W ogóle - można to rozszerzyć. Aktualnie mamy ranking, który bazuje na zdobytych punktach. Ale te można zdobyć za żarty, wpisu z dupy na mikroblogu, jakiś wpis z dosraniem innemu userowi. A jakby trochę przyjrzeć się jakości treści - np.
- odsetek postów w kategoriach technicznych
- stosunek postów do tych, które zostały docenione
- częstotliwość/regularność logowania się/pisania
- zaangażowanie w dbanie o porządek (np. raporty)

Można by było wprowadzić jakieś rangi - coś jak np. w Yanosiku stopnie, które zdobywasz za przejechane kilometry oraz potwierdzone zgłoszenia. I potem się człowiek cieszy, że jest już pułkownikiem, chociaż poza faktem posiadania plakietki na ekranie to nic z tego nie wynika :D

> Wydevelopować sposób jak ograniczyć treści powstające w działach przez nowych użytkowników.

A tak bardziej po polsku by się dało? Co chcesz ograniczać i w jakim celu? :tongue: 

> Publiczne ankiety

Proszę rozwinąć temat, bo tak średnio rozumiem, co autor miał na myśli.
Przy okazji ankiet - uważam, że udzielone odpowiedzi/statystyki powinny być niewidoczne do czasu oddania głosu przez usera. Świadomość jak głosowali inni może mieć wpływ na wynik sondy - albo ktoś pójdzie za głosem większości/zrobi tak, jak większość (bo przecież większość musi mieć rację), albo w ramach przekory zagłosuje za czymś przeciwnym. Tak czy siak - podczas wyborów mamy zakaz publikowania sondaży, tutaj powinno być podobnie.
longPost;
    }
}
