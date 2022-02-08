<template>
  <div :class="{'is-invalid': isInvalid}" class="editor">
    <vue-tabs @click="switchTab" :items="tabs" :current-tab="tabs.indexOf(currentTab)" type="pills" class="mb-2">
      <div v-if="isContent" class="btn-toolbar ml-auto">
        <div class="btn-group mr-2" role="group" aria-label="...">
          <button
            v-for="button in buttons"
            @click="button.click"
            type="button"
            class="btn btn-sm btn-control"
            :title="button.can ? button.title : button.break"
            :style="{opacity: button.can ? '1.0' : '0.4', cursor: button.can ? 'pointer' : 'default'}">
            <i :class="['fas fa-fw', button.icon]"></i>
          </button>
        </div>
      </div>
    </vue-tabs>

    <div v-show="isContent">
      <div :class="['form-control', {'is-invalid': error !== null}]"
           style="height:inherit; outline:none; box-shadow:none; border:none; padding-left:0; padding-right:0;">
        <vue-editor
          ref="editor"
          v-model="valueLocal"
          placeholder="Kliknij, aby dodać treść..."
          :autocompleteSource="autocomplete"
          v-paste:success="addAsset"
          @submit="save"
          @cancel="cancel"
          @state="updateState"/>
      </div>
      <vue-error :message="error"></vue-error>
    </div>

    <div v-show="isPreview" v-html="previewHtml" class="preview post-content"></div>

    <hr class="m-0">

    <slot name="bottom"></slot>

    <div class="row no-gutters pt-1 pl-2 pr-2">
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
          <i class="fa fab fa-markdown"/>
          Markdown jest obsługiwany
        </a>
      </div>
    </div>

    <div v-if="assets.length" class="row pt-3 pb-3 pl-2 pr-2">
      <div v-for="item in assets" :key="item.id" class="col-sm-2">
        <vue-thumbnail :url="item.url" @delete="deleteAsset(item)" @insert="insertAssetAtCaret(item)"
                       :aria-label="item.name" data-balloon-pos="down" name="asset"></vue-thumbnail>
      </div>
    </div>

    <div id="js-wiki-help" class="row collapse mt-2">
      <div class="col-md-12">
        <vue-help/>
      </div>
    </div>

    <slot></slot>
  </div>
</template>

<script lang="ts">
import {Asset} from '@/types/models';
import {link} from "@riddled/4play/index.js";
import axios from 'axios';
import Prism from 'prismjs';
import Vue from 'vue';
import Component from "vue-class-component";
import {mixin as clickaway} from 'vue-clickaway';
import {Emit, Prop, Ref, Watch} from "vue-property-decorator";
import isImage from '../../libs/assets';
import store from '../../store';
import VueError from '../forms/error.vue';
import VuePrompt from '../forms/prompt.vue';
import {default as mixin} from '../mixins/form';
import VueTabs from '../tabs.vue';
import VueThumbnail from "../thumbnail.vue";
import VueEditor from './editor.vue';
import VueHelp from './help.vue';

const CONTENT = 'Treść';
const PREVIEW = 'Podgląd';

@Component({
  mixins: [clickaway, mixin],
  components: {
    'vue-prompt': VuePrompt,
    'vue-tabs': VueTabs,
    'vue-thumbnail': VueThumbnail,
    'vue-error': VueError,
    'vue-editor': VueEditor,
    'vue-help': VueHelp,
  },
})
export default class VueMarkdown extends Vue {
  searchText: string = '';
  previewHtml: string = '';
  currentTab: string = CONTENT;
  isProcessing = false;
  progress = 0;
  tabs: string[] = [CONTENT, PREVIEW];
  buttons = {
    bold: {
      click: this.makeBold,
      can: false,
      title: 'Pogrub zaznaczony tekst lub dodaj pogrubienie',
      break: 'Dodanie tutaj pogrubienia mogłoby uszkodzić składnię',
      icon: 'fa-bold'
    },
    italics: {
      click: this.makeItalics,
      can: false,
      title: 'Pochyl tekst lub dodaj pochylenie',
      break: 'Dodanie tutaj pochylenia mogłoby uszkodzić składnię',
      icon: 'fa-italic'
    },
    underline: {
      click: this.makeUnderline,
      can: false,
      title: 'Podkreśl tekst lub dodaj podkreślenie',
      break: 'Dodanie tutaj podkreślenia mogłoby uszkodzić składnię',
      icon: 'fa-underline'
    },
    strike: {
      click: this.makeStrikeThrough,
      can: false,
      title: 'Przekreśl tekst lub dodaj przekreślenie',
      break: 'Dodanie tutaj przekreślenia mogłoby uszkodzić składnię',
      icon: 'fa-strikethrough'
    },
    link: {
      click: this.makeLink,
      can: false,
      title: 'Zamień zaznaczenie w link lub dodaj link',
      break: 'Dodanie tutaj linku mogłoby uszkodzić składnię',
      icon: 'fa-link'
    },
    image: {
      click: this.makeImage,
      can: false,
      title: 'Dodaj obraz',
      break: 'Dodanie tutaj obrazu mogłoby uszkodzić składnię',
      icon: 'fa-image'
    },
    key: {
      click: this.insertKeyNotation,
      can: false,
      title: 'Dodaj notację klawisza wprowadzonego z klawiatury',
      break: 'Dodanie tutaj znacznika klawisza mogłoby uszkodzić składnię',
      icon: 'fa-keyboard',
    },
    quote: {
      click: this.insertBlockQuote,
      can: false,
      title: 'Zmień zaznaczenie w cytat lub dodaj cytat',
      break: 'Dodanie tutaj cytatu mogłoby uszkodzić składnię',
      icon: 'fa-quote-left'
    },
    listOrdered: {
      click: this.insertOrderedList,
      can: false,
      title: 'Zmień zaznaczenie w listę lub dodaj listę uporządkowaną',
      break: 'Dodanie tutaj listy mogłoby uszkodzić składnię',
      icon: 'fa-list-ol'
    },
    listUnordered: {
      click: this.insertUnorderedList,
      can: false,
      title: 'Zmień zaznaczenie w listę lub dodaj listę nieuporządkowaną',
      break: 'Dodanie tutaj listy mogłoby uszkodzić składnię',
      icon: 'fa-list-ul'
    },
    code: {
      click: this.insertCode,
      can: false,
      title: 'Zmień zaznaczoną treść w kod lub dodaj pusty fragment kodu',
      break: 'Dodanie tutaj fragmentu kodu mogłoby uszkodzić składnię',
      icon: 'fa-code',
    },
    table: {
      click: this.insertTable,
      can: false,
      title: 'Zmień zaznaczoną treść w tabelkę lub dodaj pustą tabelkę',
      break: 'Dodanie tutaj tabelki mogłoby spowodować uszkodzenie składni',
      icon: 'fa-table'
    },
  };

  @Ref('editor')
  readonly editor!: VueEditor;

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

  addAsset(asset: Asset) {
    this.assets.push(asset);

    if (this.autoInsertAssets) {
      this.insertAssetAtCaret(asset)
    }
  }

  insertAssetAtCaret(asset: Asset) {
    if (isImage(asset.name!)) {
      this.editor.insertImage(asset.url, asset.name);
    } else {
      this.editor.insertLink(asset.url, asset.name);
    }
  }

  @Watch('value')
  clearPreview(value) {
    if (!value) {
      this.previewHtml = '';
    }
  }

  updateState(state) {
    this.buttons.bold.can = state.canBold;
    this.buttons.italics.can = state.canItalics;
    this.buttons.underline.can = state.canUnderline;
    this.buttons.strike.can = state.canStrikeThrough;
    this.buttons.listOrdered.can = state.canList;
    this.buttons.listUnordered.can = state.canList;
    this.buttons.code.can = state.canCode;
    this.buttons.table.can = state.canTable;
    this.buttons.quote.can = state.canBlockQuote;
    this.buttons.link.can = state.canLink;
    this.buttons.image.can = state.canImage;
    this.buttons.key.can = state.canKey;
  }

  autocomplete(nick) {
    return store
      .dispatch('prompt/request', {value: nick, source: this.promptUrl})
      .then(users => users.map(user => ({
        name: user.name,
        badge: user.group,
        avatar: user.photo || '/img/avatar.png',
      })));
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
  }

  makeItalics() {
    this.editor.makeItalics();
  }

  makeUnderline() {
    this.editor.makeUnderline();
  }

  makeStrikeThrough() {
    this.editor.makeStrikeThrough();
  }

  makeImage() {
    this.editor.makeImage('http://');
  }

  insertKeyNotation() {
    this.editor.makeKeyNotation('Ctrl');
  }

  makeLink() {
    this.editor.makeLink('http://');
  }

  insertBlockQuote() {
    this.editor.insertBlockQuote('Dodaj cytat...');
  }

  insertOrderedList() {
    this.editor.insertListOrdered('Pierwszy element...');
  }

  insertUnorderedList() {
    this.editor.insertListUnordered('Pierwszy element...');
  }

  insertCode() {
    this.editor.insertCodeBlock();
  }

  insertTable() {
    this.editor.addTable('Nagłówek', 'Dodaj...')
  }

  appendBlockQuote(username, postId, content) {
    const title = username + ' napisał(a)';
    const href = '/Forum/' + postId;

    this.editor.appendBlockQuote(`##### ${(link(title, href))}:\n${content}`);
  }

  appendUserMention(username) {
    this.editor.appendUserMention(username);
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

  errorNotification(errorMessage) {
    this.$notify({type: 'error', text: errorMessage});
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
}
</script>
