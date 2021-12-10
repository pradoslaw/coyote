<template>
  <div :class="{'is-invalid': isInvalid}" class="editor">
    <vue-tabs @click="switchTab" :items="tabs" :current-tab="tabs.indexOf(currentTab)" type="pills" class="mb-2">
      <div v-if="isContent" class="btn-toolbar ml-auto mt-2 mt-sm-0">
        <div class="btn-group mr-2" role="group" aria-label="...">
          <button @click="makeBold" type="button" class="btn btn-sm"
                  :title="state.canBold ? 'Dodaj pogrubienie' : 'Dodanie tutaj pogrubienia mogłoby uszkodzić składnię'"
                  :style="{opacity: state.canBold ? '1.0' : '0.4', cursor: state.canStrikeThrough ? 'pointer' : 'default'}">
            <i class="fas fa-bold fa-fw"></i>
          </button>
          <button @click="makeItalics" type="button" class="btn btn-sm"
                  :title="state.canItalics ? 'Dodaj kursywę' : 'Dodanie tutaj kursywy mogłoby uszkodzić składnię'"
                  :style="{opacity: state.canItalics ? '1.0' : '0.4', cursor: state.canStrikeThrough ? 'pointer' : 'default'}">
            <i class="fas fa-italic fa-fw"></i>
          </button>
          <button @click="makeStrikeThrough" type="button" class="btn btn-sm"
                  :title="state.canStrikeThrough ? 'Dodaj przekreślenie' : 'Dodanie tutaj przekreślenia mogłoby uszkodzić składnię'"
                  :style="{opacity: state.canStrikeThrough ? '1.0' : '0.4', cursor: state.canStrikeThrough ? 'pointer' : 'default'}">
            <i class="fas fa-strikethrough fa-fw"></i>
          </button>
          <label style="color:grey; align-self:center; margin: 3px 0 0;" title='"Smart paste" wkleja linki jako markdown'>
            <input type="checkbox" v-model="smartPaste">
            Smart paste
          </label>
        </div>
      </div>
    </vue-tabs>

    <vue-prompt v-show="isContent" :source="promptUrl">
      <div :class="['form-control', {'is-invalid': error !== null}]" style="height:inherit; outline:none; box-shadow:none; border:none;">
        <vue-editor
          ref="editor"
          v-model="valueLocal"
          placeholder="Kliknij, aby dodać treść..."
          :autocompleteSource="autocomplete"
          :smartPaste="smartPaste"
          @submit="save"
          @cancel="cancel"
          @state="updateState"
          @image="image => log(image)"/>
      </div>
      <vue-error :message="error"></vue-error>
    </vue-prompt>

    <div v-show="isPreview" v-html="previewHtml" class="preview post-content"></div>

    <hr class="m-0">

    <slot name="bottom"></slot>

    <div class="row no-gutters pt-1 pl-1 pr-1">
      <div class="small mr-auto">
        <template v-if="isProcessing">
          <i class="fas fa-spinner fa-spin small"></i>

          <span class="small">{{ progress }}%</span>
        </template>

        <a v-else :aria-label="uploadTooltip" tabindex="-1" data-balloon-length="large" data-balloon-pos="up-left"
           data-balloon-nofocus href="javascript:" class="small text-muted" @click="chooseFile">
          <i class="far fa-image"></i>

          <span class="d-none d-sm-inline">Kliknij, aby dodać załącznik lub wklej ze schowka.</span>
        </a>

        <slot name="options"></slot>
      </div>

      <div class="small ml-auto">
        <a href="#js-wiki-help" tabindex="-1" data-bs-toggle="collapse" class="small text-muted">
          <i class="fa fab fa-markdown"></i> Markdown jest obsługiwany.</a>
      </div>
    </div>

    <div v-if="assets.length" class="row pt-3 pb-3">
      <div v-for="item in assets" :key="item.id" class="col-sm-2">
        <vue-thumbnail :url="item.url" @delete="deleteAsset(item)" @insert="insertAssetAtCaret(item)"
                       :aria-label="item.name" data-balloon-pos="down" name="asset"></vue-thumbnail>
      </div>
    </div>

    <div id="js-wiki-help" class="row collapse mt-2">
      <div class="col-md-12">
        <div class="card card-info">
          <div class="card-header">Pomoc</div>
          <div class="card-body">
            <h2>Pogrubienie, kursywa...</h2>

            <p>Możesz używać pogrubienia czy kursywy, aby usprawnić czytelność tekstu: <code>**to jest
              pogrubienie**</code>, a to
              <code>*kursywa*</code>.
            </p>

            <h2>Kod źródłowy</h2>

            <p>Wszelkie jednolinijkowe instrukcje języka programowania (fragmenty kodu) powinny być zawarte pomiędzy
              obrócone
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

            <p>
              Możliwe jest tworzenie listy numerowanych oraz wypunktowanych. Wystarczy, że pierwszym znakiem linii
              będzie <code>*</code> lub <code>1. </code>
            </p>

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
              Jeżeli chcesz, możesz samodzielnie sformatować link: <code>&lt;a href="http://4programmers.net">kliknij
                tutaj&lt;/a&gt;</code>
            </p>

            <p>Możesz umieścić odnośnik do wewnętrznej podstrony, używając następującej składnii: <code>[[Delphi/Kompendium]]</code>
              lub <code>[[Delphi/Kompendium|kliknij, aby przejść do kompendium]]</code></p>

            <h2>Znaczniki HTML</h2>

            <p>Dozwolone jest używanie podstawowych znaczników HTML: &lt;a&gt;, &lt;b&gt;, &lt;i&gt;, &lt;del&gt;, &lt;strong&gt;,
              &lt;tt&gt;, &lt;dfn&gt;, &lt;ins&gt;, &lt;pre&gt;, &lt;blockquote&gt;, &lt;hr&gt;, &lt;sub&gt;, &lt;sup&gt;,
              &lt;img&gt;</p>

            <h2>Indeks górny oraz dolny</h2>

            <p>Przykład: wpisując <code>m&lt;sub&gt;2&lt;/sub&gt;,, i m&lt;sup&gt;2&lt;/sup&gt;</code> otrzymasz:
              m<sub>2</sub>
              i m<sup>2</sup>.</p>

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
  import { languages } from '../../libs/textarea';
  import { mixin as clickaway } from 'vue-clickaway';
  import { default as mixin } from '../mixins/form';
  import VueAutosize from '../../plugins/autosize';
  import VuePrompt from '../forms/prompt.vue';
  import VueTabs from '../tabs.vue';
  import VueThumbnail from "../thumbnail.vue";
  import VueError from '../forms/error.vue';
  import VueEditor from './editor.vue';
  import axios from 'axios';
  import Prism from 'prismjs';
  import IsImage from '../../libs/assets';
  import { Editor4Play } from "@riddled/4play/src/Editor.js";

  const CONTENT = 'Treść';
  const PREVIEW = 'Podgląd';

  Vue.use(VueAutosize);

  @Component({
    mixins: [clickaway, mixin],
    components: {
      'vue-prompt': VuePrompt,
      'vue-tabs': VueTabs,
      'vue-thumbnail': VueThumbnail,
      'vue-error': VueError,
      'vue-editor': VueEditor
    },
  })
  export default class VueMarkdown extends Vue {
    searchText: string = '';
    previewHtml: string = '';
    currentTab: string = CONTENT;
    isProcessing = false;
    progress = 0;
    tabs: string[] = [CONTENT, PREVIEW];
    state = {
      canBold: false,
      canItalics: false,
      canStrikeThrough: false,
      canList: false
    }
    smartPaste = false;

    @Ref('editor')
    readonly editor!: Editor4Play;

    @Ref('search')
    readonly search!: HTMLInputElement;

    @Prop()
    value!: string;

    @Prop({required: false})
    tabIndex!: number;

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

    @Prop({default: false})
    readonly isInvalid!: boolean;

    @Emit('save')
    save() {
    }

    @Emit('cancel')
    cancel() {
    }

    @Emit('paste')
    addAsset(asset: Asset) {
      this.assets.push(asset);

      if (this.autoInsertAssets) {
        this.insertAssetAtCaret(asset)
      }
    }

    insertAssetAtCaret(asset: Asset) {
      if (IsImage(asset.name!)) {
        this.editor.insertImage(asset.url, asset.name);
      } else {
        // is this ever called for non-images?
      }
    }

    @Watch('value')
    clearPreview(value) {
      if (!value) {
        this.previewHtml = '';
      }
    }

    updateState(state) {
      this.state.canBold = state.canBold;
      this.state.canItalics = state.canItalics;
      this.state.canStrikeThrough = state.canStrikeThrough;
      this.state.canList = state.canList;
    }

    autocomplete(nick) {
      return Promise.resolve([]);
    }

    log(a) {
      console.log(a);
    }

    deleteAsset(asset: Asset) {
      this.assets.splice(this.assets.indexOf(asset), 1);
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

    makeBold() {
      this.editor.makeBold();
      this.updateModel();
    }

    makeItalics() {
      this.editor.makeItalics();
      this.updateModel();
    }

    makeStrikeThrough() {
      this.editor.makeStrikeThrough();
      this.updateModel();
    }

    updateModel() {
      // this.input.dispatchEvent(new Event('input', {'bubbles': true}));
      // mam bubblować event w górę? Czy to było tylko pod <textarea>?
    }

    focus() {
      this.editor.focus();
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
