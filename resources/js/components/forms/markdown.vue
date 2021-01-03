<template>
  <div class="editor">
    <vue-tabs @click="switchTab" :items="tabs" :current-tab="tabs.indexOf(currentTab)" type="pills" class="mb-2">
      <div v-if="isContent" class="btn-toolbar ml-auto mt-2 mt-sm-0">
        <div class="btn-group mr-2" role="group" aria-label="...">
          <button @click="insertAtCaret('**', '**')" type="button" class="btn btn-sm" title="Pogrubienie"><i class="fas fa-bold fa-fw"></i></button>
          <button @click="insertAtCaret('*', '*')" type="button" class="btn btn-sm" title="Kursywa"><i class="fas fa-italic fa-fw"></i></button>
          <button @click="insertAtCaret('<u>', '</u>')" type="button" class="btn btn-sm" title="Przekreślenie"><i class="fas fa-underline fa-fw"></i></button>
          <button @click="insertAtCaret('<br>', '## ')" type="button" class="btn btn-sm" data-open="<br>" data-close="## " title="Nagłówek h2"><i class="fas fa-heading fa-fw"></i></button>
          <button @click="insertCitation" type="button" class="btn btn-sm btn-quote" title="Wstaw cytat"><i class="fas fa-quote-left fa-fw"></i></button>
        </div>

        <div class="btn-group mr-2" role="group">
          <button @click="insertAtCaret('`', '`')" type="button" class="btn btn-sm" title="Instrukcja kodu"><i class="fas fa-text-width fa-fw"></i></button>
          <button @click="insertAtCaret('```<br>', '<br>```')" type="button" class="btn btn-sm" title="Kod źródłowy"><i class="fas fa-code fa-fw"></i></button>
          <button @click="toggleDropdown" type="button" class="btn btn-sm dropdown-toggle" aria-haspopup="true" aria-expanded="false">
            <span class="caret"></span>
          </button>

          <vue-dropdown ref="dropdown" class="dropdown-menu select-menu">
            <div class="select-menu-search">
              <input v-model="searchText" @keyup.esc="hideDropdown" ref="search" type="text" class="form-control form-control-sm" placeholder="Filtruj..." autocomplete="off">
            </div>

            <div class="select-menu-wrapper">
              <ul class="list-unstyled">
                <li v-for="(value, key) in filteredLanguages">
                  <a @click="insertAtCaret('```' + key + '<br>', '<br>```<br>')">{{ value }}</a>
                </li>
              </ul>
            </div>
          </vue-dropdown>
        </div>

        <div class="btn-group mr-2" role="group">
          <button @click="insertAtCaret('<br>', '* ')" type="button" class="btn btn-sm" title="Wypunktowanie"><i class="fas fa-list-ul fa-fw"></i></button>
          <button @click="insertAtCaret('<br>', '1. ')" type="button" class="btn btn-sm" title="Numerowanie"><i class="fas fa-list-ol fa-fw"></i></button>
          <button @click="insertAtCaret('![', '](http://)')" type="button" class="btn btn-sm" title="Obraz"><i class="fas fa-image fa-fw"></i></button>
          <button @click="insertAtCaret('[', '](http://)')" type="button" class="btn btn-sm" title="Link"><i class="fas fa-link fa-fw"></i></button>
          <button @click="insertAtCaret('<br>Nagłówek 1 | Nagłówek 2<br>---------------- | -------------------<br>Kolumna1 | Kolumna 2<br>', '')" type="button" class="btn btn-sm" title="Tabela"><i class="fas fa-table fa-fw"></i></button>
        </div>
      </div>
    </vue-tabs>

    <vue-prompt v-show="isContent" :source="promptUrl">
      <textarea
        v-autosize
        v-model="valueLocal"
        v-paste:success="addAsset"
        v-paste:error="errorNotification"
        :class="{'is-invalid': error !== null}"
        @keydown.ctrl.enter="save"
        @keydown.meta.enter="save"
        @keydown.esc="cancel"
        name="text"
        class="form-control"
        ref="input"
        rows="4"
        tabindex="2"
        placeholder="Kliknij, aby dodać treść"
      ></textarea>

      <vue-error :message="error"></vue-error>
    </vue-prompt>
    <div @click="showPreview" v-show="isPreview" v-html="previewHtml" class="preview post-content"></div>

    <hr class="m-0">

    <slot name="bottom"></slot>

    <div class="row no-gutters pt-1 pl-1 pr-1">
      <div class="small mr-auto">
        <template v-if="isProcessing">
          <i class="fas fa-spinner fa-spin small"></i>

          <span class="small">{{ progress }}%</span>
        </template>

        <a v-else :aria-label="uploadTooltip" data-balloon-length="large" data-balloon-pos="up-left" data-balloon-nofocus href="javascript:" class="small text-muted" @click="chooseFile">
          <i class="far fa-image"></i>

          <span class="d-none d-sm-inline">Kliknij, aby dodać załącznik lub wklej ze schowka.</span>
        </a>

        <slot name="options"></slot>
      </div>

      <div class="small ml-auto">
        <a href="#js-wiki-help" data-toggle="collapse" class="small text-muted"><i class="fa fab fa-markdown"></i> Markdown jest obsługiwany.</a>
      </div>
    </div>

    <div v-if="assets.length" class="row pt-3 pb-3">
      <div v-for="item in assets" :key="item.id" class="col-sm-2">
        <vue-thumbnail :url="item.url" @delete="deleteAsset" :aria-label="item.name" data-balloon-pos="down" name="asset"></vue-thumbnail>
      </div>
    </div>

    <div id="js-wiki-help" class="row collapse mt-2">
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

    <slot></slot>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import { Asset } from '@/types/models';
  import { Ref, Prop, Emit, Watch } from "vue-property-decorator";
  import { default as Textarea, languages } from '../../libs/textarea';
  import { mixin as clickaway } from 'vue-clickaway';
  import { default as mixin } from '../mixins/form';
  import VueDropdownMenu from '../dropdown-menu.vue';
  import VueAutosize from '../../plugins/autosize';
  import VuePrompt from '../forms/prompt.vue';
  import VueTabs from '../tabs.vue';
  import VueThumbnail from "../thumbnail.vue";
  import VueError from '../forms/error.vue';
  import axios from 'axios';
  import Prism from 'prismjs';
  import IsImage from '../../libs/assets';

  const CONTENT = 'Treść';
  const PREVIEW = 'Podgląd';

  Vue.use(VueAutosize);

  @Component({
    mixins: [ clickaway, mixin ],
    components: {
      'vue-dropdown': VueDropdownMenu,
      'vue-prompt': VuePrompt ,
      'vue-tabs': VueTabs,
      'vue-thumbnail': VueThumbnail,
      'vue-error': VueError
    },
  })
  export default class VueMarkdown extends Vue {
    textarea!: Textarea;
    searchText: string = '';
    previewHtml: string = '';
    currentTab: string = CONTENT;
    isProcessing = false;
    progress = 0;
    tabs: string[] = [CONTENT, PREVIEW];

    @Ref('input')
    readonly input!: HTMLTextAreaElement;

    @Ref('search')
    readonly search!: HTMLInputElement;

    @Ref('dropdown')
    readonly dropdown!: VueDropdownMenu;

    @Prop()
    value!: string;

    @Prop({default: 20})
    readonly uploadMaxSize!: number;

    @Prop({default: 'jpg, jpeg, gif, png, zip, rar, txt, pdf, doc, docx, xls, xlsx, cpp, 7z, 7zip, patch, webm'})
    readonly uploadMimes!: string;

    @Prop({default: true})
    readonly autoInsertAssets!: boolean;

    @Prop({default: '/completion/prompt/users'})
    readonly promptUrl!: string;

    @Prop()
    readonly previewUrl!: string;

    @Prop({default: null})
    readonly error: string | null = null;

    @Prop({default: () => []})
    readonly assets!: Asset[];

    @Emit('save')
    save() {}

    @Emit('cancel')
    cancel() {}

    @Emit('paste')
    addAsset(asset: Asset) {
      this.assets.push(asset);

      if (this.autoInsertAssets) {
        this.insertAtCaret((IsImage(asset.name!) ? '!' : '') + '[' + asset.name + '](' + asset.url + ')', '');
      }
    }

    @Watch('value')
    clearPreview(value) {
      if (value === '') {
        this.previewHtml = '';
      }
    }

    deleteAsset(url: string) {
      this.assets.splice(this.assets.findIndex(item => item.url === url), 1);
    }

    mounted() {
      this.textarea = new Textarea(this.input);
    }

    chooseFile() {
      const Thumbnail = new VueThumbnail({propsData: {name: 'asset'}}).$mount();

      this.progress = 0;

      Thumbnail.$on('upload', this.addAsset);
      Thumbnail.$on('progress', progress => {
        this.progress = progress;
        this.isProcessing = progress > 0 && progress < 100
      });

      Thumbnail.openDialog();
    }

    insertAtCaret(startsWith, endsWith) {
      this.textarea.insertAtCaret(startsWith.replace(/<br>/g, "\n"), endsWith.replace(/<br>/g, "\n"), this.textarea.isSelected() ? this.textarea.getSelection() : '');

      if (this.dropdown.isDropdownVisible) {
        this.hideDropdown();
      }

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

    switchTab(index: number) {
      this.currentTab = this.tabs[index];

      if (this.tabs[index] === PREVIEW) {
        this.showPreview();
      }
    }

    showPreview() {
      axios.post(this.previewUrl, {text: this.value}).then(response => {
        this.previewHtml = response.data;

        this.$nextTick(() => Prism.highlightAll());
      });
    }

    errorNotification(err) {
      this.$notify({'type': 'error', 'text': err});
    }

    get uploadTooltip() {
      return `Maksymalnie ${this.uploadMaxSize}MB. Dostępne rozszerzenia: ${this.uploadMimes}`;
    }

    get isContent() {
      return this.currentTab == CONTENT;
    }

    get isPreview() {
      return this.currentTab === PREVIEW;
    }

    get filteredLanguages() {
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
