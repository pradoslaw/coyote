<template>
  <div v-on-clickaway="blurInput" :class="{'nav-search-mobile': isMobile}" class="nav-search">
    <div :class="{'active': isActive}" class="search-bar ml-md-4 mr-md-4">
      <i class="fas fa-magnifying-glass ml-2 mr-2"></i>

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
          v-model="value"
          type="search"
          name="q"
          autocomplete="off"
          placeholder="Wpisz &quot;?&quot;, aby uzyskać pomoc lub wyszukaj"
        >
      </form>

      <!-- close mobile menu -->
      <button v-if="isMobile" @click="toggleMobile" class="btn nav-link">
        <i class="fa fa-2x fa-xmark"></i>
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
<!--&lt;!&ndash;      <div v-if="isDropdownVisible && items.length > 0" class="search-dropdown">&ndash;&gt;-->
<!--&lt;!&ndash;        <nav class="list-inline" style="margin: 10px 7px 10px 7px">&ndash;&gt;-->
<!--&lt;!&ndash;          <a class="list-inline-item active mr-2 text-primary" href="#" style="padding: 5px; font-size: 90%; border-bottom: 2px solid #80a41a">Forum</a>&ndash;&gt;-->
<!--&lt;!&ndash;          <a class="list-inline-item text-body" href="#" style="padding: 5px; font-size: 90%;">Praca</a>&ndash;&gt;-->
<!--&lt;!&ndash;        </nav>&ndash;&gt;-->

        <ul class="list-unstyled">
          <template v-for="category in categories">
            <li class="title"><span>{{ categoryLabel(category) }}</span></li>

            <li v-for="child in category.children" :class="{'hover': child.index === selectedIndex}" @mouseover="hoverItem(child.index)">
              <component :is="makeDecorator(child)" :item="child" :value="value"></component>
            </li>
          </template>
        </ul>
      </div>
    </div>

    <!-- show this only on mobile devices to show search bar -->
    <div v-if="!isMobile" class="d-md-none navbar-nav ml-auto mr-2">
      <a @click="toggleMobile" href="javascript:" class="nav-link">
        <i class="fa fa-magnifying-glass fa-fw"></i>
      </a>
    </div>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import { mixin as clickaway } from 'vue-clickaway';
  import axios from 'axios';
  import store from '../../store';
  import { Hit } from '@/types/hit';
  import { SpecialKeys } from '@/types/keys';
  import Component from 'vue-class-component';
  import { Prop, Ref, Watch } from 'vue-property-decorator';
  import { mapGetters } from "vuex";
  import VueTopicDecorator from './decorators/topic.vue';
  import VueJobDecorator from './decorators/job';
  import VueMicroblogDecorator from './decorators/microblog';
  import VueWikiDecorator from './decorators/wiki';
  import VueUserDecorator from './decorators/user.vue';
  import { HitCategory, Contexts, Models } from '@/types/search';

  // @ts-ignore
  @Component({
    mixins: [ clickaway ],
    store,
    computed: mapGetters('user', ['isAuthorized']),
    components: {
      'vue-topic-decorator': VueTopicDecorator,
      'vue-job-decorator': VueJobDecorator,
      'vue-wiki-decorator': VueWikiDecorator,
      'vue-microblog-decorator': VueMicroblogDecorator,
      'vue-user-decorator': VueUserDecorator
    }
  })
  export default class VueSearchbar extends Vue {
    isActive: boolean = false;
    isDropdownVisible: boolean = false;
    isHelpEnabled: boolean = false;
    isMobile: boolean = false;
    items: Hit[] = [];
    selectedIndex: number = -1;
    params?: URLSearchParams;

    readonly isAuthorized! : boolean;

    @Prop()
    value!: string;

    @Ref('input')
    readonly input!: HTMLInputElement;

    @Watch('value')
    resetItems(val: string) {
      if (val === '') {
        this.items = [];
      }
    }

    created() {
      this.makeParams();
    }

    mounted() {
      document.addEventListener('keydown', this.shortcutSupport);

      // @ts-ignore
      history.onpushstate = window.onpopstate = () => {
        // wait for location to really change before setting up new url
        setTimeout(() => this.makeParams(), 0);
      }
    }

    beforeDestroy() {
      document.removeEventListener('keydown', this.shortcutSupport);
    }

    toggleMobile() {
      this.isMobile = ! this.isMobile;

      if (this.isMobile) {
        this.$nextTick(() => (this.$refs.input as HTMLInputElement).focus());
      }
    }

    showDropdown() {
      this.isActive = true;
      this.isDropdownVisible = true;

      // show basic set of links for given user even if no query was provided
      this.loadItems();
    }

    hideDropdown() {
      if (this.isDropdownVisible) {
        this.isDropdownVisible = false;
      }
      else {
        this.value = '';
      }
    }

    blurInput() {
      this.isActive = false;
    }

    clearInput() {
      this.value = '';
      this.loadItems();
    }

    down() {
      this.isDropdownVisible = true;

      this.changeIndex(++this.selectedIndex);
    }

    up() {
      this.changeIndex(--this.selectedIndex);
    }

    hoverItem(index) {
      this.selectedIndex = index;
    }

    changeIndex(index) {
      const length = this.items.length;

      if (length > 0) {
        if (index >= length) {
          index = 0;
        }
        else if (index < 0) {
          index = length - 1;
        }

        this.selectedIndex = index;
      }
    }

    categoryLabel(category: Hit): string {
      return category.context !== undefined ? Contexts[category.model][category.context] : Models[category.model];
    }

    completion(event: KeyboardEvent): void {
      this.isHelpEnabled = this.value === '?';

      if (this.isHelpEnabled || Object.values(SpecialKeys).includes(event.keyCode)) {
        return;
      }

      this.selectedIndex = -1; // reset position index after key pressed
      this.loadItems();
    }

    loadItems(): void {
      const headers = this.isAuthorized ? {Authorization: `Bearer ${this.$store.state.user.user.token}`} : {};

      if (!this.endpoint) {
        return;
      }

      axios.get(this.endpoint, {params: {q: this.value || null}, headers}).then(response => {
        this.items = response.data;
        this.isDropdownVisible = true;
      });
    }

    shortcutSupport(event: KeyboardEvent): void {
      if (event.key === '?' && event.shiftKey && this.elementSupportSearchShortcut(event)) {
        event.preventDefault();

        (this.$refs.input as HTMLInputElement).focus();
      }
    }

    private elementSupportSearchShortcut(event: KeyboardEvent) {
      const htmlElement = event.target as HTMLElement;
      if (htmlElement.isContentEditable) {
        return !htmlElement.closest(".editor-4play"); // make sure we found 4play editor
      }
      return !/^(?:input|textarea|select|button)$/i.test(htmlElement.tagName);
    }

    changeUrl(): void {
      if (this.selectedIndex === -1) {
        (this.$refs.search as HTMLFormElement).submit();

        return;
      }

      window.location.href = this.items.find(item => item.index === this.selectedIndex)!.url;
    }

    makeParams(): void {
      if (window.location.pathname !== '/Search') {
        this.params = undefined;

        return;
      }

      const params = new URLSearchParams(window.location.search);
      params.delete('q');
      params.delete('page');

      this.params = params;
    }

    makeDecorator(item: Hit): string {
      return `vue-${item.model.toLowerCase()}-decorator`;
    }

    get endpoint(): string | null {
      return this.value.trim() === '' ? (this.isAuthorized ? '/completion/hub/' : null) : '/completion/';
    }

    get categories() {
      let counter = 0;
      let result: HitCategory = {};

      this.items.forEach(item => {
        const key = `${item.model}-${item.context}`;
        let model, context;

        ({model, context} = item);

        if (!result[key]) {
          result[key] = {children: [], model, context}
        }

        result[key].children.push(item);
      });

      return Object.values(result).map(category => {
        category.children.forEach(child => Object.assign(child, {index: counter++}));

        return category;
      });
    }
  }
</script>
