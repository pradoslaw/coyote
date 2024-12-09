<template>
  <div v-click-away="blurInput" :class="{'nav-search-mobile': isMobile}" class="nav-search">
    <div :class="{'active': isActive}" class="search-bar ms-md-4 me-md-4 neon-navbar-search-bar">
      <span class="ms-2 me-2">
        <vue-icon name="autocompleteSearch"/>
      </span>
      <form action="/Search" role="search" ref="search" class="flex-grow-1">
        <input v-for="[key, value] of params" type="hidden" :name="key" :value="value">
        <input
          ref="input"
          @focus="showDropdown"
          @keydown.esc.prevent="hideDropdown"
          @input="completion"
          @keyup.up.prevent="up"
          @keyup.down.prevent="down"
          @keydown.enter.prevent="changeUrl"
          @search="clearInput"
          v-model="innerValue"
          type="search"
          name="q"
          autocomplete="off"
          placeholder="Wpisz &quot;?&quot;, aby uzyskać pomoc lub wyszukaj"
        >
      </form>
      <button v-if="isMobile" @click="toggleMobile" class="btn nav-link">
        <span style="font-size:2em;">
          <vue-icon name="mobileSearchClose"/>
        </span>
      </button>
      <div v-if="isHelpEnabled" class="search-dropdown p-3">
        <div class="row">
          <div class="col-6 mb-2">
            <code>"foo bar"</code> <small class="text-muted">szukaj całych fraz</small>
          </div>
          <div class="col-6 mb-2">
            <code>+foo -bar</code> <small class="text-muted">wyklucz lub żądaj danego słowa w dokumencie</small>
          </div>
          <div class="col-6 mb-2">
            <code>foo*</code> <small class="text-muted">szuka fragmentów słów</small>
          </div>
          <div class="col-6 mb-2">
            <code>foo~</code> <small class="text-muted">szuka podobnych słów</small>
          </div>
          <div class="col-6 mb-2">
            <code>user:foo</code> <small class="text-muted">szukaj po autorze</small>
          </div>
        </div>
      </div>

      <div v-else-if="isDropdownVisible && items.length > 0" class="search-dropdown">
        <ul class="list-unstyled">
          <template v-for="category in categories">
            <li class="title">
              <span>{{ categoryLabel(category) }}</span>
            </li>
            <li v-for="child in category.children" :class="{'hover': child.index === selectedIndex}" @mouseover="hoverItem(child.index)">
              <component :is="makeDecorator(child)" :item="child" :value="innerValue"></component>
            </li>
          </template>
        </ul>
      </div>
    </div>

    <div v-if="!isMobile" class="d-md-none navbar-nav ms-auto me-2">
      <a @click="toggleMobile" href="javascript:" class="nav-link">
        <vue-icon name="navigationSearch"/>
      </a>
    </div>
  </div>
</template>

<script lang="ts">
import axios, {AxiosResponse} from 'axios';
import {mapGetters} from 'vuex';

import clickAway from "../../clickAway.js";
import store from '../../store/index';
import {Hit} from '../../types/hit';
import {SpecialKeys} from '../../types/keys';
import {Contexts, HitCategory, Models} from '../../types/search';
import {nextTick} from "../../vue";
import VueIcon from "../icon";
import VueJobDecorator from './decorators/job';
import VueMicroblogDecorator from './decorators/microblog';
import VueTopicDecorator from './decorators/topic.vue';
import VueUserDecorator from './decorators/user.vue';
import VueWikiDecorator from './decorators/wiki';

export default {
  directives: {clickAway},
  store,
  components: {
    'vue-icon': VueIcon,
    'vue-job-decorator': VueJobDecorator,
    'vue-microblog-decorator': VueMicroblogDecorator,
    'vue-topic-decorator': VueTopicDecorator,
    'vue-user-decorator': VueUserDecorator,
    'vue-wiki-decorator': VueWikiDecorator,
  },
  props: {
    value: String,
  },
  data() {
    return {
      isActive: false,
      isDropdownVisible: false,
      isHelpEnabled: false,
      isMobile: false,
      items: [] as Hit[],
      selectedIndex: -1,
      params: undefined,
      innerValue: '',
    };
  },
  watch: {
    innerValue(val: string) {
      if (val === '') {
        this.items = [];
      }
    },
  },
  created(this: Vue): void {
    this.makeParams();
    this.$data.innerValue = this.$props.value!;
  },
  mounted() {
    document.addEventListener('keydown', this.shortcutSupport);

    history.onpushstate = window.onpopstate = () => {
      // wait for location to really change before setting up new url
      setTimeout(() => this.makeParams(), 0);
    };
  },
  beforeUnmount() {
    document.removeEventListener('keydown', this.shortcutSupport);
  },
  methods: {
    toggleMobile() {
      this.isMobile = !this.isMobile;
      if (this.isMobile) {
        nextTick(() => (this.$refs.input as HTMLInputElement).focus());
      }
    },
    showDropdown() {
      this.isActive = true;
      this.isDropdownVisible = true;
      this.loadItems();
    },
    hideDropdown() {
      if (this.isDropdownVisible) {
        this.isDropdownVisible = false;
      } else {
        this.innerValue = '';
      }
    },
    blurInput() {
      this.isActive = false;
    },
    clearInput() {
      this.innerValue = '';
      this.loadItems();
    },
    down() {
      this.isDropdownVisible = true;
      this.changeIndex(++this.selectedIndex);
    },
    up() {
      this.changeIndex(--this.selectedIndex);
    },
    hoverItem(index) {
      this.selectedIndex = index;
    },
    changeIndex(index) {
      const length = this.items.length;

      if (length > 0) {
        if (index >= length) {
          index = 0;
        } else if (index < 0) {
          index = length - 1;
        }

        this.selectedIndex = index;
      }
    },
    categoryLabel(category: Hit): string {
      return category.context !== undefined ? Contexts[category.model][category.context] : Models[category.model];
    },
    completion(event: KeyboardEvent): void {
      this.isHelpEnabled = this.innerValue === '?';

      if (this.isHelpEnabled || Object.values(SpecialKeys).includes(event.keyCode)) {
        return;
      }

      this.selectedIndex = -1; // reset position index after key pressed
      this.loadItems();
    },
    loadItems(): void {
      const headers: Record<string, string> = {};
      if (this.isAuthorized) {
        headers.Authorization = 'Bearer ' + store.state.user.user.token;
      }
      if (!this.endpoint) {
        return;
      }

      axios.get<any>(this.endpoint, {params: {q: this.innerValue || null}, headers}).then((response: AxiosResponse<any>) => {
        this.items = response.data;
        this.isDropdownVisible = true;
      });
    },
    shortcutSupport(event: KeyboardEvent): void {
      if (event.key === '?' && event.shiftKey && this.elementSupportSearchShortcut(event)) {
        event.preventDefault();
        (this.$refs.input as HTMLInputElement).focus();
      }
    },
    elementSupportSearchShortcut(event: KeyboardEvent) {
      const htmlElement = event.target as HTMLElement;
      if (htmlElement.isContentEditable) {
        return !htmlElement.closest(".editor-4play"); // make sure we found 4play editor
      }
      return !/^(?:input|textarea|select|button)$/i.test(htmlElement.tagName);
    },
    changeUrl(): void {
      if (this.selectedIndex === -1) {
        (this.$refs.search as HTMLFormElement).submit();
        return;
      }

      window.location.href = this.items.find(item => item.index === this.selectedIndex)!.url;
    },
    makeParams(): void {
      if (window.location.pathname !== '/Search') {
        this.params = undefined;
        return;
      }

      const params = new URLSearchParams(window.location.search);
      params.delete('q');
      params.delete('page');
      this.params = params;
    },
    makeDecorator(item: Hit): string {
      return `vue-${item.model.toLowerCase()}-decorator`;
    },
  },
  computed: {
    ...mapGetters('user', ['isAuthorized']),
    endpoint(): string | null {
      return this.innerValue.trim() === '' ? (this.isAuthorized ? '/completion/hub/' : null) : '/completion/';
    },
    categories() {
      let counter = 0;
      let result: HitCategory = {};

      this.items.forEach(item => {
        const key = `${item.model}-${item.context}`;
        let model, context;

        ({model, context} = item);

        if (!result[key]) {
          result[key] = {children: [], model, context};
        }

        result[key].children.push(item);
      });

      return Object.values(result).map(category => {
        category.children.forEach(child => Object.assign(child, {index: counter++}));
        return category;
      });
    },
  },
};
</script>
