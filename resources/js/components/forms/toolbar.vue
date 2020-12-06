<template>
  <div>
    <div id="wiki-toolbar" class="row">
      <div class="col-md-12">
        <div class="btn-group" role="group" aria-label="...">
          <button @click="insertAtCaret('**', '**')" type="button" class="btn btn-sm btn-secondary" title="Pogrubienie"><i class="fas fa-bold fa-fw"></i></button>
          <button @click="insertAtCaret('*', '*')" type="button" class="btn btn-sm btn-secondary" title="Kursywa"><i class="fas fa-italic fa-fw"></i></button>
          <button @click="insertAtCaret('<u>', '</u>')" type="button" class="btn btn-sm btn-secondary" title="Przekreślenie"><i class="fas fa-underline fa-fw"></i></button>
          <button @click="insertAtCaret('`', '`')" type="button" class="btn btn-sm btn-secondary" title="Instrukcja kodu"><i class="fas fa-text-width fa-fw"></i></button>
        </div>

        <div class="btn-group" role="group">
          <button @click="insertAtCaret('<br>', '## ')" type="button" class="btn btn-sm btn-secondary" data-open="<br>" data-close="## " title="Nagłówek h2"><i class="fas fa-heading fa-fw"></i></button>
        </div>

        <div class="btn-group" role="group">
          <button @click="insertAtCaret('<br>', '*')" type="button" class="btn btn-sm btn-secondary" title="Wypunktowanie"><i class="fas fa-list-ul fa-fw"></i></button>
          <button @click="insertAtCaret('<br>', '1. ')" type="button" class="btn btn-sm btn-secondary" title="Numerowanie"><i class="fas fa-list-ol fa-fw"></i></button>
          <button @click="insertAtCaret('![title', '](http://)')" type="button" class="btn btn-sm btn-secondary" title="Obraz"><i class="fas fa-image fa-fw"></i></button>
          <button @click="insertAtCaret('[', '](http://)')" type="button" class="btn btn-sm btn-secondary" title="Link"><i class="fas fa-link fa-fw"></i></button>
          <button @click="insertAtCaret('<br>Nagłówek 1 | Nagłówek 2<br>---------------- | -------------------<br>Kolumna1 | Kolumna 2<br>', '')" type="button" class="btn btn-sm btn-secondary" title="Tabela"><i class="fas fa-table fa-fw"></i></button>
          <button @click="insertCitation" type="button" class="btn btn-sm btn-secondary btn-quote" title="Wstaw cytat"><i class="fas fa-quote-left fa-fw"></i></button>

          <div id="select-menu" class="btn-group">
            <button @click="insertAtCaret('```<br>', '<br>```')" type="button" class="btn btn-sm btn-secondary" title="Kod źródłowy"><i class="fas fa-code fa-fw"></i></button>
            <button @click="toggleDropdown" type="button" class="btn btn-sm btn-secondary dropdown-toggle" aria-haspopup="true" aria-expanded="false">
              <span class="caret"></span>
            </button>

            <vue-dropdown-menu ref="dropdown" class="dropdown-menu select-menu">
              <div class="select-menu-search">
                <input v-model="searchText" @keyup.esc="hideDropdown" ref="search" type="text" class="form-control form-control-sm" placeholder="Filtruj..." autocomplete="off">
              </div>
              <div class="select-menu-wrapper">
                <ul class="list-unstyled">
                  <li v-for="(value, key) in filteredValue">
                    <a @click="insertAtCaret('```' + key + '<br>', '<br>```<br>')">{{ value }}</a>
                  </li>
                </ul>
              </div>
            </vue-dropdown-menu>
          </div>
        </div>

        <button type="button" class="btn btn-sm btn-secondary float-right" data-toggle="collapse" data-target="#wiki-help" aria-expanded="false" aria-controls="wiki-help"><i class="fas fa-question fa-fw"></i></button>
      </div>
    </div>

    <div id="wiki-help" class="row collapse">
      <div class="col-md-12">
        <div class="card card-info">
          <div class="card-header">Pomoc</div>
          <div class="card-body">
            <h2>Pogrubienie, kursywa...</h2>

            <p>Możesz używać pogrubienia czy kursywy, aby usprawnić czytelność tekstu: <code>**to jest pogrubienie**</code>, a to
              <code>*kursywa*</code>.
            </p>

            <h2>Kod źródłowy</h2>

            <p>Wszelkie jednolinijkowe instrukcje języka programowania (fragmenty kodu) powinny być zawarte pomiędzy obrócone
              apostrofy
              lub podwójny cudzysłów, czyli: <code>`kod instrukcji języka programowania`</code>.</p>

            <p><code>```</code> umożliwia kolorowanie większych fragmentów kodu. Możemy nadać nazwę języka
              programowania,
              aby system użył konkretnych ustawień kolorowania składnii:
              <br/><br/>
              <code>
                ```javascript<br/>
                &nbsp;&nbsp;document.write('Hello World');<br/>
                ```<br/>
              </code></p>

            <h2>Nagłówki</h2>

            <p>
              <code>## Nagłówek 2</code><br/>
              <code>### Nagłówek 3</code><br/>
              <code>#### Nagłówek 4</code>
            </p>

            <h2>Wypunktowanie i numerowanie</h2>

            <p>Możliwe jest tworzenie listy numerowanych oraz wypunktowanych. Wystarczy, że pierwszym znakiem linii będzie
              <code>*</code> lub <code>1. </code></p>

            <p>
              <code>1. Lista numerowana</code><br/>
              <code>2. Lista numerowana</code><br/>
            </p>
            <p></p>
            <p>
              <code>* Lista wypunktowana</code><br/>
              <code>* Lista wypunktowana</code><br/>
              <code>** Lista wypunktowana (drugi poziom)</code><br/>
            </p>

            <h2>Linki</h2>

            <p>URL umieszczony w tekście zostanie przez system automatycznie wykryty i zamieniony na znacznik <code>
              &lt;a&gt;</code>.<br/>
              Jeżeli chcesz, możesz samodzielnie sformatować link: <code>&lt;a href="http://4programmers.net">kliknij tutaj&lt;/a&gt;</code>
            </p>

            <p>Możesz umieścić odnośnik do wewnętrznej podstrony, używając następującej składnii: <code>[[Delphi/Kompendium]]</code>
              lub <code>[[Delphi/Kompendium|kliknij, aby przejść do kompendium]]</code></p>

            <h2>Znaczniki HTML</h2>

            <p>Dozwolone jest używanie podstawowych znaczników HTML: &lt;a&gt;, &lt;b&gt;, &lt;i&gt;, &lt;del&gt;, &lt;strong&gt;,
              &lt;tt&gt;, &lt;dfn&gt;, &lt;ins&gt;, &lt;pre&gt;, &lt;blockquote&gt;, &lt;hr&gt;, &lt;sub&gt;, &lt;sup&gt;, &lt;img&gt;</p>

            <h2>Indeks górny oraz dolny</h2>

            <p>Przykład: wpisując <code>m&lt;sub&gt;2&lt;/sub&gt;,, i m&lt;sup&gt;2&lt;/sup&gt;</code> otrzymasz: m<sub>2</sub> i m<sup>2</sup>.</p>

            <h2>Składnia Tex</h2>

            <p><code>&lt;tex&gt;arcctg(x) = argtan(\frac{1}{x}) = arcsin(\frac{1}{\sqrt{1+x^2}})&lt;/tex&gt;</code></p>
          </div>
        </div>
      </div>
    </div>

    <vue-prompt :source="source">
      <textarea
        v-autosize
        v-model="valueLocal"
        v-paste:success="paste"
        :class="{'is-invalid': isInvalid}"
        @keydown.ctrl.enter="save"
        @keydown.meta.enter="save"
        @keydown.esc="cancel"
        name="text"
        class="form-control"
        ref="input"
        rows="4"
        tabindex="2"
        placeholder="Kliknij, aby dodać treść"
        data-popover='{"placement": "top", "offset": "16%,14px", "message": "Markdown jest obsługiwany. Ctrl+V wkleja obraz ze schowka."}'
      ></textarea>

      <slot></slot>
    </vue-prompt>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import { Ref, Prop, Emit, Watch } from "vue-property-decorator";
  import { default as Textarea, languages } from '../../libs/textarea';
  import { mixin as clickaway } from 'vue-clickaway';
  import { default as mixin } from '../mixins/form';
  import VueDropdownMenu from '../dropdown-menu.vue';
  import VuePaste from "../../plugins/paste";
  import VueAutosize from '../../plugins/autosize';
  import VuePrompt from '../forms/prompt.vue';

  Vue.use(VuePaste, {url: '/Forum/Paste'});
  Vue.use(VueAutosize);

  @Component({
    mixins: [ clickaway, mixin ],
    components: { 'vue-dropdown-menu': VueDropdownMenu, 'vue-prompt': VuePrompt },
  })
  export default class VueToolbar extends Vue {
    textarea!: Textarea;
    searchText: string = '';
    isDropdownVisible: boolean = false;

    @Ref('input')
    readonly input!: HTMLTextAreaElement;

    @Ref('search')
    readonly search!: HTMLInputElement;

    @Ref('dropdown')
    readonly dropdown!: VueDropdownMenu;

    @Prop()
    value!: string;

    @Prop({default: '/completion/prompt'})
    readonly source!: string;

    @Prop()
    readonly isInvalid: boolean = false;

    @Emit('save')
    save() {}

    @Emit('cancel')
    cancel() {}

    @Emit('paste')
    paste() {}

    mounted() {
      this.textarea = new Textarea(this.input);
    }

    insertAtCaret(startsWith, endsWith) {
      this.textarea.insertAtCaret(startsWith.replace(/<br>/g, "\n"), endsWith.replace(/<br>/g, "\n"), this.textarea.isSelected() ? this.textarea.getSelection() : '');
      this.isDropdownVisible = false;

      this.updateModel();
    }

    insertCitation() {
      this.textarea.insertAtCaret('> ', '', this.textarea.getSelection().replace(/\r\n/g, "\n").replace(/\n/g, "\n> "));

      this.updateModel();
    }

    toggleDropdown() {
      this.dropdown.toggle();
      this.$nextTick(() => this.search.focus());
    }

    hideDropdown() {
      this.dropdown.toggle();
    }

    updateModel() {
      this.input.dispatchEvent(new Event('input', {'bubbles': true}));
    }

    focus() {
      this.input.focus();
    }

    get filteredValue() {
      return Object
        .keys(languages)
        .filter(language => languages[language].toLowerCase().startsWith(this.searchText.toLowerCase()))
        .reduce((obj, key) => {
          obj[key] = languages[key];

          return obj;
        }, {});
    }
  }
</script>
