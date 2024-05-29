<?php
namespace Coyote\Domain\User;

use Coyote\Domain\Html;
use Coyote\Domain\StringHtml;

class UserSettings
{
    public function marketingAgreement(): Html
    {
        $gdpr = '<a href="mailto:gdpr@4programmers.net">gdpr@4programmers.net</a>';
        $_4programmers = '<a href="/">4programmers.net</a>';

        return new StringHtml("Wyrażam zgodę na otrzymywanie, na podany przeze mnie adres e-mail, informacji handlowych
            kierowanych do mnie przez 4programmers.net (tj. Makana sp. z o.o., z siedzibą przy ul. Krupniczej 13, 50-075
            Wrocław). Informacje handlowe dotyczyć będą produktów, usług i działalności realizowanej przez
            4programmers.net i jej kontrahentów. Rozumiem, że zgodę mogę wycofać w dowolnym momencie, jednak nie będzie
            to miało wpływu na przetwarzanie, którego dokonano przed jej wycofaniem. Przedmiotowa zgoda może zostać
            wycofana poprzez odznaczenie jej w ustawieniach mojego konta albo poprzez wysłanie stosownej wiadomości na
            adres e-mail: $gdpr lub adres siedziby $_4programmers.");
    }

    public function newsletterAgreement(): Html
    {
        return new StringHtml('Zgadzam się na otrzymywanie newslettera.');
    }

    public function termsAndPrivacyPolicyAgreement(): Html
    {
        $terms = '<a href="/Regulamin">regulamin</a>';
        $privacyPolicy = '<a href="/Polityka_prywatności">politykę prywatności</a>';
        return new StringHtml("Akceptuję $terms oraz $privacyPolicy.<b>*</b>");
    }

    public function informationClause(): Html
    {
        $privacyPolicy = '<a href="/Polityka_prywatności">polityce prywatności</a>';
        return new StringHtml(
            "Uzupełnieniając pola oznaczone jako dobrowolne oraz klikając \"Zapisz\" wyrażasz swoją dobrowolną
            zgodę na przetwarzanie wpisanych w nich danych osobowych w celu umieszczenia ich w Twoim profilu. Zgodę
            można wycofać w każdej chwili poprzez usunięcie danych w koncie, co nie wpływa na zgodność z prawem
            przetwarzania dokonanego przed jej wycofaniem. Więcej informacji o przetwarzaniu danych osobowych oraz
            Twoich prawach z tym związanych możesz znaleźć w $privacyPolicy.");
    }

    public function cookieAgreement(): Html
    {
        $_4programmers = '<a href="/">4programmers.net</a>';
        $privacyPolicy = '<a href="/Polityka_prywatności">polityce prywatności</a>';

        return new StringHtml(
            "Na forum $_4programmers korzystamy z plików cookies. Część z nich jest niezbędna do funkcjonowania
             naszego forum, natomiast wykorzystanie pozostałych zależy od Twojej dobrowolnej zgody, którą możesz 
             wyrazić poniżej. Klikając „Zaakceptuj Wszystkie” zgadzasz się na wykorzystywanie przez nas plików cookies 
             analitycznych oraz reklamowych, jeżeli nie chcesz udzielić nam swojej zgody kliknij „Tylko niezbędne”. 
             Możesz także wyrazić swoją zgodę odrębnie dla plików cookies analitycznych lub reklamowych. W tym celu 
             ustaw odpowiednio pola wyboru i kliknij „Zaakceptuj Zaznaczone”. Więcej informacji o technologii cookie 
             znajduje się w naszej $privacyPolicy.");
    }
}
