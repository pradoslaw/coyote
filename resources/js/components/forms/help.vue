<template>
  <div class="markdown-help">
    <div class="card card-info">
      <div class="card-header">Pomoc <span class="card-version">1.11.7</span></div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h3>Typografia</h3>
            <p>
              Edytor obsługuje składnie Markdown, w której pojedynczy akcent <code>*kursywa*</code>
              oraz <code>_kursywa_</code> to pochylenie. Z kolei podwójny akcent <code>**pogrubienie**</code>
              oraz <code>__pogrubienie__</code> to pogrubienie. Dodanie znaczników <code>~~strike~~</code>
              to przekreślenie.
            </p>
            <p>
              Możesz dodać formatowanie komendami <i class="fas fa-fw fa-bold"/>,
              <i class="fas fa-fw fa-italic"/>, <i class="fas fa-fw fa-underline"/> oraz
              <i class="fas fa-fw fa-strikethrough"/>.
            </p>
            <p class="text-muted">
              Ponieważ dekoracja podkreślenia jest przeznaczona na linki, markdown
              nie zawiera specjalnej składni dla podkreślenia. Dlatego by dodać
              podkreślenie, użyj <code>&lt;u&gt;underline&lt;/u&gt;</code>.
            </p>
            <p>
              Komendy formatujące reagują na skróty klawiszowe: <kbd>Ctrl+B</kbd>,
              <kbd>Ctrl+I</kbd>, <kbd>Ctrl+U</kbd> oraz <kbd>Ctrl+S</kbd>.
            </p>

            <h3>Linki</h3>
            <p>
              By dodać link w edytorze użyj komendy <i class="fa fa-link"/> lub
              użyj składni <code>[title](link)</code>. URL umieszczony w linku lub nawet
              URL umieszczony bezpośrednio w tekście będzie aktywny i klikalny.
            </p>
            <p>
              Jeżeli chcesz, możesz samodzielnie dodać link: <code>&lt;a href="link"&gt;title&lt;/a&gt;</code>.
            </p>

            <h4>Wewnętrzne odnośniki</h4>
            <p>
              Możesz umieścić odnośnik do wewnętrznej podstrony, używając następującej
              składnii: <code>[[Delphi/Kompendium]]</code> lub <code>[[Delphi/Kompendium|kliknij,
              aby przejść do kompendium]]</code>. Odnośniki mogą prowadzić do Forum 4programmers
              lub np. do Kompendium.
            </p>

            <h3>Wspomnienia użytkowników</h3>
            <p>
              By wspomnieć użytkownika forum, wpisz w formularzu znak <code>@</code>. Zobaczysz
              okienko autouzupełniające nazwy użytkowników. Autouzupełnienie dobierze odpowiedni
              format wspomnienia, zależnie od tego czy w nazwie użytkownika znajduje się spacja.
            </p>

            <h3>Znaczniki HTML</h3>
            <p>
              Dozwolone jest używanie niektórych znaczników HTML: <code>&lt;a&gt;</code>,
              <code>&lt;b&gt;</code>, <code>&lt;i&gt;</code>, <code>&lt;kbd&gt;</code>, <code>&lt;del&gt;</code>,
              <code>&lt;strong&gt;</code>, <code>&lt;dfn&gt;</code>, <code>&lt;pre&gt;</code>,
              <code>&lt;blockquote&gt;</code>,
              <code>&lt;hr/&gt;</code>, <code>&lt;sub&gt;</code>, <code>&lt;sup&gt;</code> oraz
              <code>&lt;img/&gt;</code>.
            </p>

            <h4>Skróty klawiszowe</h4>
            <p>
              Reprezentuj kombinacje klawiszowe używając taga <code>&lt;kbd&gt;</code>. Oddziel
              klawisze w kombinacji plusem, oraz kombinacje klawiszy od siebie przecinkiem.
            </p>
            <p>
              Dodaj kombinację klawiszy komendą notacji klawiszy <i class="fas fa-keyboard"></i>
              lub skrótem klawiszowym <kbd>Alt+K</kbd>.
            </p>
            <blockquote>
              W Visual Studio, do przesunięcia linijki użyj code użyj skrótu
              <code>&lt;kbd&gt;Ctrl+K,Ctrl+D&lt;/kbd&gt;</code>.
            </blockquote>

            <h4>Indeks górny oraz dolny</h4>
            <p>
              Przykład: wpisując <code>H&lt;sub&gt;2&lt;/sub&gt;O</code> i <code>m&lt;sup&gt;2&lt;/sup&gt;</code> otrzymasz:
              H<sub>2</sub>O i m<sup>2</sup>.
            </p>

            <h3>Składnia Tex</h3>
            <p>
              By precyzyjnie wyrazić działanie matematyczne, użyj składni Tex.
            </p>
            <p>
              <code>&lt;tex&gt;arcctg(x) = argtan(\frac{1}{x}) = arcsin(\frac{1}{\sqrt{1+x^2}})&lt;/tex&gt;</code>
            </p>
          </div>
          <div class="col-md-6">
            <h3>Kod źródłowy</h3>

            <h4>Krótkie fragmenty kodu</h4>
            <p>
              Wszelkie jednolinijkowe instrukcje języka programowania powinny być zawarte
              pomiędzy obrócone apostrofy: <code>`kod instrukcji`</code> lub
              <code>``console.log(`template`);``</code>.
            </p>

            <h4>Kod wielolinijkowy</h4>
            <p>
              Dodaj fragment kodu komendą <i class="fas fa-fw fa-code"></i>. Fragmenty
              kodu zajmujące całą lub więcej linijek powinny być umieszczone w wielolinijkowym
              fragmencie kodu. Znaczniki <code>```</code> lub <code>~~~</code> umożliwiają
              kolorowanie różnych języków programowania. Możemy nadać nazwę języka programowania
              używając auto-uzupełnienia, kod został pokolorowany używając konkretnych ustawień
              kolorowania składnii:
            </p>
            <p>
              <code>
                ```javascript<br/>
                document.write('Hello World');<br/>
                ```<br/>
              </code>
            </p>
            <p>
              Możesz zaznaczyć również już wklejony kod w edytorze, i użyć
              komendy&nbsp;<i class="fas fa-fw fa-code"></i> by zamienić go w kod. Możesz użyć też kombinacji
              <kbd>Ctrl+Alt+C</kbd>, by dodać fragment kodu bez oznaczników języka.
            </p>

            <h3>Tabelki</h3>
            <p>
              Dodaj przykładową tabelkę używając komendy <i class="fas fa-fw fa-table"/>. Przykładowa tabelka
              składa się z dwóch kolumn, nagłówka i jednego wiersza.
            </p>
            <p>
              Wygeneruj tabelkę na podstawie tzw. "blueprint". Oddziel komórki separatorem <code>;</code> lub <code>|</code>,
              a następnie zaznacz kursorem blueprint.
            </p>
            <p>
              <code>nazwisko;dziedzina;odkrycie</code><br>
              <code>Pitagoras;mathematics;Pythagorean Theorem</code><br>
              <code>Albert Einstein;physics;General Relativity</code><br>
              <code>Marie Curie, Pierre Curie;chemistry;Radium, Polonium</code><br>
            </p>

            <p>Użyj komendy <i class="fas fa-fw fa-table"/> by zamienić zaznaczony blueprint na tabelkę Markdown.</p>

            <h3>Lista uporządkowana i nieuporządkowana</h3>
            <p>
              Możliwe jest tworzenie listy numerowanych oraz wypunktowanych. Wystarczy, że pierwszym znakiem linii
              będzie <code>*</code> lub <code>-</code> dla listy nieuporządkowanej oraz <code>1. </code> dla
              listy uporządkowanej.
            </p>
            <p>Użyj komendy <i class="fas fa-fw fa-list-ol"/> by dodać listę uporządkowaną.</p>
            <p>
              <code>1. Lista numerowana</code><br/>
              <code>2. Lista numerowana</code><br/>
            </p>
            <p>Użyj komendy <i class="fas fa-fw fa-list-ul"/> by dodać listę nieuporządkowaną.</p>
            <p>
              <code>* Lista wypunktowana</code><br/>
              <code>* Lista wypunktowana</code><br/>
              <code>** Lista wypunktowana (drugi poziom)</code><br/>
            </p>
          </div>
          <div class="col-md-6">
            <h3>Składnia Markdown</h3>
            <p>
              Edytor obsługuje składnię Markdown, która składa się ze znaków specjalnych.
              Dostępne komendy, jak formatowanie <i class="fas fa-fw fa-bold"/>, dodanie
              tabelki <i class="fas fa-fw fa-table"/> lub fragmentu kodu <i class="fas fa-fw fa-code"/>
              są w pewnym sensie swiadome otaczającej jej składni, i postarają się uniknać
              uszkodzenia jej.
            </p>
            <p>
              Dla przykładu, używając tylko dostępnych komend, nie możemy dodać formatowania
              pogrubienia do kodu wielolinijkowego, albo dodać listy do tabelki - mogłoby
              to doprowadzić do uszkodzenia składni.
            </p>
            <p>
              W pewnych odosobnionych przypadkach brak nowej linii przed elementami markdown
              również mógłby uszkodzić składnie, dlatego edytor dodaje brakujące nowe linie.
              Dla przykładu, dodanie formatowania pochylenia zaraz po tabelce, mogłoby zostać
              błędne zinterpretowane, więc edytor doda oddzielającą nową linię pomiędzy tabelką,
              a pochyleniem.
            </p>
          </div>
          <div class="col-md-6">
            <h3>Skróty klawiszowe</h3>

            <p>
              Skróty formatujące, kiedy w edytorze znajduje się pojedynczy kursor, wstawiają
              sformatowany tekst przykładowy. Jeśli w edytorze znajduje się zaznaczenie (słowo,
              linijka, paragraf), wtedy zaznaczenie zostaje sformatowane.
            </p>
            <ul>
              <li><kbd>Ctrl+B</kbd> - dodaj pogrubienie lub pogrub zaznaczenie</li>
              <li><kbd>Ctrl+I</kbd> - dodaj pochylenie lub pochyl zaznaczenie</li>
              <li><kbd>Ctrl+U</kbd> - dodaj podkreślenie lub podkreśl zaznaczenie</li>
              <li><kbd>Ctrl+S</kbd> - dodaj przekreślenie lub przekreśl zaznaczenie</li>
            </ul>

            <p>Notacja Klawiszy</p>
            <ul>
              <li><kbd>Alt+K</kbd> - dodaj notację klawiszy</li>
            </ul>

            <p>Fragment kodu bez oznacznika</p>
            <ul>
              <li><kbd>Ctrl+Alt+C</kbd>/<kbd>⌘+Alt+C</kbd> - dodaj pusty fragment kodu</li>
            </ul>

            <p>Skróty operujące na kodzie i linijkach:</p>
            <ul>
              <li><kbd>Alt+L</kbd> - zaznaczenie całej linii</li>
              <li>
                <kbd>Alt+<i class="fas fa-arrow-up"/></kbd>, <kbd>Alt+<i class="fas fa-arrow-down"/></kbd> -
                przeniesienie linijki w której znajduje się kursor w górę/dół.
              </li>
              <li><kbd>Tab</kbd>/<kbd>⌘+]</kbd> - dodaj wcięcie (wcięcie w prawo)</li>
              <li><kbd>Shit+Tab</kbd>/<kbd>⌘+[</kbd> - usunięcie wcięcia (wycięcie w lewo)</li>
            </ul>

            <p>Dodawanie postów:</p>
            <ul>
              <li><kbd>Ctrl+Enter</kbd> - dodaj post</li>
              <li><kbd>⌘+Enter</kbd> - dodaj post (MacOS)</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
