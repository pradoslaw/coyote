##2.3

**Ogólne**
- Upgrade do Laravel 5.7
- Rezygnacja z MongoDB na rzecz PostgreSQL + Elasticsearch
- Upgrade do Webpack 4, rezygnacja z Gulp
- Rezygnacja z Geshi na rzecz Prism
- Przepisanie wiadomości prywatnych na Vue.js
- Wiadomości prywatne w formie czatu

**Forum**
- Zapisywanie informacji o powodzie usunięcia postów, w tabeli posts
- Zapisywanie informacji o dacie przeniesienia/zablokowania wątku, w tabeli topics
- Na stronie głównej, ostatnia aktywność na forum prezentuje tylko wątki z kategorii, które nie są przez danego użytkownika niewidoczne
- Przepisanie strony głównej na Vue.js
- Możliwość ukrywania kategorii oraz ich przesuwania z poziomu strony głównej

**Praca**
- Możliwość komentowania ofert pracy
- Ustawienie sortowania nie jest zapamiętywane (domyślnie sortowanie zawsze po dacie)
- W przypadku wyszukiwania, ustawienie sortowania na "Trafność" zamiast "Data dodania"
- Nowy wygląd strony głównej
- Możliwość ściągnięcia CV bezpośrednio ze strony z ofertą
- Powiadomienie o nowej kandydaturze na dane stanowisko, nie tylko poprzez e-mail
- Komentarze do ofert pracy

## 2.2

**Ogólne**
- Upgrade do Laravel 5.4
- "Przyklejony" breadcrumb nie znika na ekranie po pewnym czasie nieaktywności
- Użycie intervention/image do tworzenia miniatur obrazów
- Upgrade do PHP 7.2

**Praca**

- W e-mailu informującym o zakończeniu publikacji ogłoszenia, dodanie opcji ponownego wystawienia oferty
- Zmiana algorytmu wyświetlającego reklamy ofert pracy (teraz bazuje na historii danego usera)
- Wyświetlanie logo danego języka przy filtrach
- Możliwość dodawania zdjęć (oraz filmu) do ogłoszeń pracy
- Grupowanie wymagań w ofertach pracy (tagi) w kategorie
- Dodanie limitu 5 MB w załączniku w aplikacjach ofert pracy
- Możliwość zapamiętywania danych wprowadzonych w formularzu
- Zwiększenie rozmiarów loga firm
- Graficzna zmiana maila informującego o nowej aplikacji na dane stanowisko

**Panel administracjyjny**

- Możliwość edycji drugiego (długiego) tytułu dla kategorii forum

**Forum**

- Blokada edycji oraz usuwania postów (jeżeli nie jest ostatnim w danym wątku) jeżeli użytkownik ma mniej niż 100 punktów reputacji.
- Użytkownik ma możliwość dalszej edycji swojego postu mimo tego, że napisane zostały dalsze odpowiedzi (pod warunkiem, że nie upłynęło 24h)

## 2.1

**Ogólne**
- Upgrade do Laravel 5.3
- Usunięcie bowera na rzecz npm
- Webpack + babel zamiast samego babela
- Znacznik `<u>` jest ponownie dozwolony przez parser
- Zmiana sposobu wyświetlania listy użytkowników online (https://github.com/adam-boduch/coyote/issues/174)
- Obsługa sesji poprzez Redis
- Zmiana sposobu autentyfikacji użytkownika na serwerze WebSocket: cookie z ID sesji zamiast tokena

**Panel użytkownika**
- Grupowanie wiadomości po nazwie użytkownika (jeden użytkownik = jedna konwersacja)
- Okno powiadomień oraz wiadomości prywatnych na urzędzeniach mobilnych: wyświetlanie na całą szerokość okna
- Lista mikroblogów użytkownika widoczna w jego profilu
- Możliwość zapisania linku do konta github

**Forum**
- Wyświetlanie tagów do wątków, nawet na małych ekranach
- Delikatnie powiększenie czcionki tagów kosztem paddingu
- Grupowanie ustawień powiadomień w panelu użytkownika

**Mikroblogu**

- Link do głosowania na komentarz zmienia kolor jeżeli oddano na niego głos

**Praca**
- Wyszukiwarka w module "Praca" wyszukuje nazwy miejscowości zapisane bez polskich znaków
- Optymalizacja wyszukiwania tagów w formularzu dodawania nowej oferty
- Sugerowanie tagów w formularza nowej oferty pracy
- Wynagrodzenie na liście ofert, w dziale Praca prezentowane jest w ujęciu miesięcznym (mimo, że w ofercie zostało określone np. godzinowo)
- Lista ofert pracy w dziale Praca uwzględnia w drobnym stopniu Twoją aktualną lokalizację (prezentując oferty pracy zdalnej lub bliskie Twojemu miejscu zamieszkania)
- Formularz dodawania nowego ogłoszenia: przypisany na Vue.js
- Nowe pole z możliwością określania stażu pracy w ogłoszeniu (junior, seniot itp)
- Formularz aplikacji o pracę: możliwość wysłania linka do konta Github
- Logo w ogłoszeniu jest linkiem, który prowadzi do wszystkich ofert danej firmy
- Link do powrotu do listy ofert w ogłoszeniu o pracę
- Wyczyszczenie pola wyszukiwania powoduje przeładowanie formularza
- Zmiana w algorytmie sortowania: losowe "wypychanie" starszych ofert na górę listy
- Możliwość określania stawki brutto lub netto
- Dodanie obrazka "Bądź pierwszy" pod przyciskiem do aplikowania na ofertę pracy
- Oddzielenie ofert darmowych od promowanych

## 2.0

**Ogólne**:
- Responsywny layout
- Zmiana nagłówka strony (mniejszy rozmiar w pionie, wyświetlanie avatara użytkownika, menu)
- Obsługa markdown w całym miejscu serwisu (wiadomości prywatne, forum, mikroblogi, praca)
- Możliwość logowania/rejestrowania poprzez OAuth Github, Facebook oraz Google
- Drobne poprawki graficzne w stosunku do wersji 1.x
- Powiadomienia w czasie rzeczywistym dzięki użyciu HTML5 WebSockets
- Tzw. "sticky header" domyślnie dla wszystkich (bez możliwości wyłączenia)

**Mikroblogi**:
- Możliwość obserwowania/zaptrzestania obserwacji konkretnego wpisu
- Opcja *Automatycznie obserwuj wątki oraz wpisy na mikroblogu, w których biorę udział*
- Możliwość dodawania wielu załączników graficznych (screeny)
- Ctrl+V w polu edycji/dodawania wpisu na mikrobloga wkleja obraz ze schowka
- Zmieniony layout mikroblogów
- Usuwanie opcji dodawania "znajomych"

**Panel użytkownika**:
- Generowanie podglądu wiadomości prywatnej
- Możliwość wklejania obrazów ze schowka poprzez Ctrl+V
- Obsługa markdown
- Infinite scroll ładujący poprzednie wiadomości prywatne z danym użytkownikiem
- Poprawa wydajności
- Możliwość określenia nazwy firmy w której pracuje użytkownik (oraz zajmowanego stanowiska)
- Zmieniony wygląd profilu użytkownika

**Praca**:
- Całkowicie przebudowany wygląd oraz działanie działu *Praca*
- Możliwość wyszukiwania osobno - po słowach kluczowych czy nazwie miejscowości
- Nowa zakładka - *Wybrane dla mnie* która daje możliwość pokazywania jedynie wybranych przez użytkownika - ofert pracy
- Domyślne sortowanie po trafności danej oferty (zmiana algorytmu sortowania)
- Tagi przypisane do oferty mogą być grupowane na *Wymagane* oraz *Mile widziane*
- Generowanie podglądu przed dodaniem oferty pracy
- Usunięcie pola "Wymagania i obowiązki" z formularza dodawania nowej oferty
- Nowy walidator nazwy miejscowości
- Algorytm korygowania nazw miejscowości. Np. Warsaw => Warszawa, Poznan => Poznań itp
- Lepsze geokodowanie nazw miejscowości
- Możliwość zgłaszania nieprawidłowych ofert pracy (czyli takich gdzie potrzebna jest interwencja moderatora)
- Dodatkowe pole na podanie minimalnego wynagrodzenia przy aplikowaniu o pracę

**Pastebin**:
- Możliwośc nadawania tytułów wpisów
- Usuwanie wpisów bez konieczności logowania się do panelu administracyjnego

**Forum**:
- Na liście wątków, temat który zawiera raporty jest oznaczany kolorem czerwonym oraz odpowiednią ikoną
- Możliwość zamknięcia raportu (dla moderatorów) z poziomu danego postu
- Dla moderatorów: dziennik zdarzeń wygląda teraz inaczej oraz zawiera więcej informacji o akcji w danym wątku
- Generowanie losowych nicków dla użytkowników anonimowych: dodanie większej ilości kombinacji
- Użytkownicy z reputacją powyzej 100 pkt nie muszą czekać ani sekundy przed dodaniem kolejnego posta
- Usunięcie możliwości eksportowania widoków do rss (docelowo zastąpione API)
- Wątki na stronie główne nie są wyświetlane jeżeli są zablokowane lub w zamkniętych kategoriach forum
