## 2.0

**Ogólne**:
- Responsywny layout
- Zmiana nagłówka strony (mniejszy rozmiar w pionie, wyświetlanie avatara użytkownika, menu)
- Obsługa markdown w całym miejscu serwisu (wiadomości prywatne, forum, mikroblogi, praca)
- Możliwość logowania/rejestrowania poprzez OAuth Github, Facebook oraz Google
- Drobne poprawki graficzne w stosunku do wersji 1.x
- Powiadomienia w czasie rzeczywistym dzięki użyciu WebSockets

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
