<template>
  <div :class="{'is-invalid': isInvalid}" class="editor">
    <vue-tabs @click="switchTab" :items="tabs" :current-tab="tabs.indexOf(currentTab)" type="pills" class="mb-2">
      <div v-if="isContent" class="btn-toolbar ms-auto">
        <div class="btn-group d-inline me-2 ms-2 mt-1" role="group" aria-label="...">
          <button
            v-for="button in buttons"
            @click="button.click"
            type="button"
            class="btn btn-sm btn-control"
            :title="button.can ? button.title : button.break"
            :style="{opacity: button.can ? '1.0' : '0.4', cursor: button.can ? 'pointer' : 'default'}">
            <i :class="['fas fa-fw', button.icon]"/>
          </button>
        </div>
      </div>
    </vue-tabs>

    <div style="position:relative">
      <div v-show="isContent">
        <div :class="['form-control', {'is-invalid': error !== null}]"
             style="height:inherit; outline:none; box-shadow:none; border:none; padding-left:0; padding-right:0;">
          <vue-editor
            ref="editor"
            v-model="valueLocal"
            placeholder="Kliknij, aby dodać treść..."
            :autocompleteSource="autocomplete"
            v-paste:success="addAsset"
            :emojiUrl="emojiUrl"
            :emojis="emojisProperty"
            @submit="save"
            @cancel="cancel"
            @state="updateState"/>
        </div>
        <vue-error :message="error"/>
      </div>

      <div v-show="isPreview" v-html="previewHtml" class="preview post-content"/>

      <div v-show="emojiPickerOpen" class="emoji-picker-container">
        <vue-emoji-picker
          ref="emoji-picker"
          :emojis="emojis"
          :open="emojiPickerOpen"
          @input="insertEmoji"
          @blur="blurEmojiPicker"
          @close="closeEmojiPicker"/>
      </div>
    </div>

    <hr class="m-0"/>
    <slot name="bottom"/>

    <div class="pt-1 ps-2 pe-2 d-flex">
      <div class="small me-auto">
        <template v-if="isProcessing">
          <i class="fas fa-spinner fa-spin small"/>
          <span class="small">{{ progress }}%</span>
        </template>

        <a v-else :aria-label="uploadTooltip" tabindex="-1" data-balloon-length="large" data-balloon-pos="up-left"
           data-balloon-nofocus href="javascript:" class="small text-muted" @click="chooseFile">
          <i class="far fa-image"/>
          <span class="d-none d-sm-inline">Kliknij, aby dodać załącznik lub wklej ze schowka.</span>
        </a>

        <slot name="options"/>
      </div>

      <div class="small ms-auto">
        <a href="#js-wiki-help" tabindex="-1" data-bs-toggle="collapse" class="small text-muted">
          <i class="fab fa-markdown"/>
          Instrukcja obsługi Markdown
        </a>
      </div>
    </div>

    <div v-if="assets.length" class="pt-3 pb-3 ps-2 pe-2 d-flex">
      <div v-for="item in assets" :key="item.id" class="col-sm-2">
        <vue-thumbnail
          name="asset"
          data-balloon-pos="down"
          :url="item.url"
          :aria-label="item.name"
          @delete="deleteAsset(item)"
          @insert="insertAssetAtCaret(item)"/>
      </div>
    </div>

    <div id="js-wiki-help" class="mt-2 collapse">
      <vue-help/>
    </div>

    <slot/>
  </div>
</template>

<script lang="ts">
import {link} from "@riddled/4play/index.js";
import axios from 'axios';
import Prism from 'prismjs';
import Vue from "vue";

import isImage from '../../libs/assets';
import {pasteDirective} from "../../plugins/paste.js";
import store from '../../store';
import VueError from '../forms/error.vue';
import VuePrompt from '../forms/prompt.vue';
import {default as formMixin} from '../mixins/form.js';
import VueTabs from '../tabs.vue';
import VueThumbnail from "../thumbnail.vue";
import VueEditor from './editor.vue';
import VueEmojiPicker from './emoji-picker.vue';
import VueHelp from './help.vue';

const CONTENT = 'Treść';
const PREVIEW = 'Podgląd';

export default {
  name: 'VueMarkdown',
  mixins: [formMixin],
  components: {
    'vue-prompt': VuePrompt,
    'vue-tabs': VueTabs,
    'vue-thumbnail': VueThumbnail,
    'vue-error': VueError,
    'vue-editor': VueEditor,
    'vue-help': VueHelp,
    'vue-emoji-picker': VueEmojiPicker,
  },
  directives: {
    paste: pasteDirective('/assets'),
  },
  props: {
    value: String,
    tabIndex: {
      type: Number,
      required: false,
    },
    uploadMaxSize: {
      type: Number,
      default: 20,
    },
    uploadMimes: {
      type: String,
      default: 'jpg, jpeg, gif, png, zip, rar, txt, pdf, doc, docx, xls, xlsx, cpp, 7z, 7zip, patch, webm',
    },
    autoInsertAssets: {
      type: Boolean,
      default: true,
    },
    promptUrl: {
      type: String,
      default: '/completion/prompt/users',
    },
    previewUrl: {
      type: String,
      required: true,
    },
    error: {
      type: String,
      default: null,
    },
    assets: {
      type: Array,
      default: () => [],
    },
    isInvalid: {
      type: Boolean,
      default: false,
    },
    emojis: {
      type: Object,
      required: false,
    },
  },
  data() {
    return {
      previewHtml: '',
      currentTab: CONTENT,
      isProcessing: false,
      progress: 0,
      tabs: [CONTENT, PREVIEW],
      emojiPickerOpen: false,
      buttons: {
        bold: {
          click: this.makeBold,
          can: false,
          title: 'Pogrub zaznaczony tekst lub dodaj pogrubienie',
          break: 'Dodanie tutaj pogrubienia mogłoby uszkodzić składnię',
          icon: 'fa-bold',
        },
        italics: {
          click: this.makeItalics,
          can: false,
          title: 'Pochyl tekst lub dodaj pochylenie',
          break: 'Dodanie tutaj pochylenia mogłoby uszkodzić składnię',
          icon: 'fa-italic',
        },
        underline: {
          click: this.makeUnderline,
          can: false,
          title: 'Podkreśl tekst lub dodaj podkreślenie',
          break: 'Dodanie tutaj podkreślenia mogłoby uszkodzić składnię',
          icon: 'fa-underline',
        },
        strike: {
          click: this.makeStrikeThrough,
          can: false,
          title: 'Przekreśl tekst lub dodaj przekreślenie',
          break: 'Dodanie tutaj przekreślenia mogłoby uszkodzić składnię',
          icon: 'fa-strikethrough',
        },
        link: {
          click: this.makeLink,
          can: false,
          title: 'Zamień zaznaczenie w link lub dodaj link',
          break: 'Dodanie tutaj linku mogłoby uszkodzić składnię',
          icon: 'fa-link',
        },
        code: {
          click: this.insertCode,
          can: false,
          title: 'Zmień zaznaczoną treść w kod lub dodaj pusty znacznik (kod źródłowy, błąd, stack-trace, wynik działania programu itp.)',
          break: 'Dodanie tutaj tekstu preformatowanego mogłoby uszkodzić składnię',
          icon: 'fa-code',
        },
        image: {
          click: this.makeImage,
          can: false,
          title: 'Dodaj obraz',
          break: 'Dodanie tutaj obrazu mogłoby uszkodzić składnię',
          icon: 'fa-image',
        },
        key: {
          click: this.insertKeyNotation,
          can: false,
          title: 'Dodaj notację klawisza wprowadzonego z klawiatury',
          break: 'Dodanie tutaj znacznika klawisza mogłoby uszkodzić składnię',
          icon: 'fa-keyboard',
        },
        listOrdered: {
          click: this.insertOrderedList,
          can: false,
          title: 'Zmień zaznaczenie w listę lub dodaj listę uporządkowaną',
          break: 'Dodanie tutaj listy mogłoby uszkodzić składnię',
          icon: 'fa-list-ol',
        },
        listUnordered: {
          click: this.insertUnorderedList,
          can: false,
          title: 'Zmień zaznaczenie w listę lub dodaj listę nieuporządkowaną',
          break: 'Dodanie tutaj listy mogłoby uszkodzić składnię',
          icon: 'fa-list-ul',
        },
        quote: {
          click: this.insertBlockQuote,
          can: false,
          title: 'Cytuj post innego użytkownika lub zmień zaznaczenie w cytat postu',
          break: 'Dodanie tutaj cytatu mogłoby uszkodzić składnię',
          icon: 'fa-quote-left',
        },
        table: {
          click: this.insertTable,
          can: false,
          title: 'Zmień zaznaczoną treść w tabelkę lub dodaj pustą tabelkę',
          break: 'Dodanie tutaj tabelki mogłoby spowodować uszkodzenie składni',
          icon: 'fa-table',
        },
        indentLess: {
          click: this.indentLess,
          can: false,
          title: 'Usuń wcięcie zaznaczonego tekstu',
          break: null,
          icon: 'fa-outdent',
        },
        indentMore: {
          click: this.indentMore,
          can: false,
          title: 'Dodaj wcięcie zaznaczonego tekstu',
          break: null,
          icon: 'fa-indent',
        },
        insertEmoji: {
          click: this.toggleEmojiPicker,
          can: false,
          title: 'Dodaj emotikonę',
          break: 'Dodanie tutaj emotikony mogłoby spowodować uszkodzenie składni',
          icon: 'fa-face-smile-beam',
        },
      },
    };
  },
  methods: {
    makeBold() {
      this.$refs.editor.makeBold();
    },
    makeItalics() {
      this.$refs.editor.makeItalics();
    },
    makeUnderline() {
      this.$refs.editor.makeUnderline();
    },
    makeStrikeThrough() {
      this.$refs.editor.makeStrikeThrough();
    },
    makeLink() {
      this.$refs.editor.makeLink('http://');
    },
    makeImage() {
      this.$refs.editor.makeImage('http://');
    },
    insertKeyNotation() {
      this.$refs.editor.makeKeyNotation('Ctrl');
    },
    insertBlockQuote() {
      this.$refs.editor.insertBlockQuote('Dodaj cytat...');
    },
    insertOrderedList() {
      this.$refs.editor.insertListOrdered('Pierwszy element...');
    },
    insertUnorderedList() {
      this.$refs.editor.insertListUnordered('Pierwszy element...');
    },
    insertCode() {
      this.$refs.editor.insertCodeBlock();
    },
    insertTable() {
      this.$refs.editor.addTable('Nagłówek', 'Dodaj...');
    },
    indentMore() {
      this.$refs.editor.indentMore();
    },
    indentLess() {
      this.$refs.editor.indentLess();
    },
    insertEmoji(emoji) {
      this.$refs.editor.insertEmoji(emoji);
    },
    appendBlockQuote(username, postId, content) {
      const title = username + ' napisał(a)';
      const href = '/Forum/' + postId;

      this.$refs.editor.appendBlockQuote(`##### ${(link(title, href))}:\n${content}`);
    },
    appendUserMention(username) {
      this.$refs.editor.appendUserMention(username);
    },
    toggleEmojiPicker() {
      this.emojiPickerOpen = !this.emojiPickerOpen;
      this.$nextTick(() => {
        if (this.$refs['emoji-picker']) {
          this.$refs['emoji-picker'].clearSearchPhrase();
        }
      });
    },
    blurEmojiPicker(event) {
      if (!this.isEmojiToggleControl(event.target as HTMLElement)) {
        this.closeEmojiPicker();
      }
    },
    closeEmojiPicker() {
      this.emojiPickerOpen = false;
    },
    isEmojiToggleControl(element) {
      if (element.title === 'Dodaj emotikonę') {
        return true;
      }
      if (element.parentElement) {
        if (element.parentElement.title === 'Dodaj emotikonę') {
          return true;
        }
      }
      return false;
    },
    emojiUrl(emojiName) {
      const emoticon = this.emojis?.emoticons[emojiName];
      if (emoticon) {
        return 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/svg/' + emoticon.unified + '.svg';
      }
      return null;
    },
    addAsset(asset) {
      this.assets.push(asset);

      if (this.autoInsertAssets) {
        this.insertAssetAtCaret(asset);
      }
    },
    insertAssetAtCaret(asset) {
      if (isImage(asset.name!)) {
        this.$refs.editor.insertImage(asset.url, asset.name!);
      } else {
        this.$refs.editor.insertLink(asset.url, asset.name!);
      }
    },
    deleteAsset(asset) {
      this.assets.splice(this.assets.indexOf(asset), 1);
    },
    chooseFile() {
      const Thumbnail = new (Vue.extend(VueThumbnail))({propsData: {name: 'asset'}}).$mount();

      this.progress = 0;

      Thumbnail.$on('upload', this.addAsset);
      Thumbnail.$on('progress', progress => {
        this.progress = progress;
        this.isProcessing = progress > 0 && progress < 100;
      });

      Thumbnail.openDialog();
    },
    autocomplete(nick) {
      return store
        .dispatch('prompt/request', {value: nick, source: this.promptUrl})
        .then(users => users.map(user => ({
          name: user.name,
          badge: user.group,
          avatar: user.photo || '/img/avatar.png',
        })));
    },
    save() {
      this.$emit('save');
    },
    cancel() {
      this.$emit('cancel');
    },
    focus() {
      this.$refs.editor.focus();
    },
    switchTab(index) {
      this.currentTab = this.tabs[index];

      if (this.tabs[index] === PREVIEW) {
        this.showPreview();
      }
    },
    showPreview() {
      axios.post<any>(this.previewUrl, {text: this.value}).then(response => {
        this.previewHtml = response.data as string;
        this.$nextTick(() => Prism.highlightAll());
      });
    },
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
      this.buttons.indentMore.can = state.canIndent;
      this.buttons.indentLess.can = state.canIndent;
      this.buttons.insertEmoji.can = state.canEmoji;
    },
    errorNotification(errorMessage) {
      this.$notify({type: 'error', text: errorMessage});
    },
  },
  watch: {
    value(newValue) {
      if (!newValue) {
        this.previewHtml = '';
      }
    },
  },
  computed: {
    uploadTooltip() {
      return `Maksymalnie ${this.uploadMaxSize}MB. Dostępne rozszerzenia: ${this.uploadMimes}`;
    },
    isContent() {
      return this.currentTab == CONTENT;
    },
    isPreview() {
      return this.currentTab === PREVIEW;
    },
    emojisProperty() {
      const {categories, subcategories, emoticons} = this.emojis!;
      const emojis = {};
      for (const category of categories) {
        for (const subcategory of category.subcategories) {
          for (const emoticon of subcategories[subcategory]) {
            const emoji = emoticons[emoticon];
            emojis[':' + emoji.id + ':'] = emoji.name;
          }
        }
      }
      return emojis;
    },
  },
  updated() {
    this.$nextTick(() => Prism.highlightAll());
  },
  mounted() {
    this.$nextTick(() => Prism.highlightAll());
  },
};
</script>
