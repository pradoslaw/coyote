<template>
  <div v-on-clickaway="blurInput" class="nav-search">
    <div :class="{'search-bar-active': isActive}" class="search-bar">
      <i class="fas fa-search ml-2 mr-2"></i>

      <form :action="url" role="search" ref="search" class="flex-grow-1">
        <input
          ref="input"
          @focus="showDropdown"
          @keydown.esc.prevent="hideDropdown"
          @keyup="completion"
          @keyup.up.prevent="up"
          @keyup.down.prevent="down"
          @keydown.enter.prevent="changeUrl"
          @search="clearInput"
          v-model="value"
          type="search"
          name="q"
          autocomplete="off"
          placeholder="Kliknij, aby wyszukać lub wpisz /"
        >
      </form>

      <div v-if="isDropdownVisible && items.length > 0" class="search-dropdown">
<!--&lt;!&ndash;      <div v-if="isDropdownVisible && items.length > 0" class="search-dropdown">&ndash;&gt;-->
<!--&lt;!&ndash;        <nav class="list-inline" style="margin: 10px 7px 10px 7px">&ndash;&gt;-->
<!--&lt;!&ndash;          <a class="list-inline-item active mr-2 text-primary" href="#" style="padding: 5px; font-size: 90%; border-bottom: 2px solid #80a41a">Forum</a>&ndash;&gt;-->
<!--&lt;!&ndash;          <a class="list-inline-item text-body" href="#" style="padding: 5px; font-size: 90%;">Praca</a>&ndash;&gt;-->
<!--&lt;!&ndash;        </nav>&ndash;&gt;-->

        <ul class="list-unstyled">
          <template v-for="category in categories">
            <li class="title"><span>{{ getCategoryLabel(category) }}</span></li>

            <li v-for="child in category.children" :class="{'hover': child.index === selectedIndex}" @mouseover="hoverItem(child.index)">
              <component :is="makeDecorator(child)" :item="child" :value="value"></component>
            </li>

<!--&lt;!&ndash;            <li v-if="contexts.length > 0" class="more">&ndash;&gt;-->
<!--&lt;!&ndash;              <a href="#">więcej ...</a>&ndash;&gt;-->
<!--&lt;!&ndash;            </li>&ndash;&gt;-->
          </template>
        </ul>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
  import VueAvatar from '../avatar.vue';
  import Vue from 'vue';
  import { mixin as clickaway } from 'vue-clickaway';
  import axios from 'axios';
  import store from '../../store';
  import { Hit, Context } from '../../types/hit';
  import { Model } from '../../types/models';
  import { SpecialKeys } from '../../types/keys';
  import Component from 'vue-class-component';
  import { Prop, PropSync, Ref } from 'vue-property-decorator';

  const SLASH = '/';

  type HitCategory = {[key: string]: {children: Hit[], model: string, context: string}}

  type ModelType = {
    [key in Model]: string;
  };

  type ContextType = {
    [key in Context]: string;
  }

  type ModelContextType = {
    [key in Model]: ContextType;
  };

  const CONTEXTS: ModelContextType = {
    [Model.Topic]: {
      [Context.User]: 'Twoje wątki',
      [Context.Subscriber]: 'Obserwowane wątki',
      [Context.Participant]: 'Twoje dyskusje',
    },
    [Model.User]: {
      [Context.User]: '',
      [Context.Subscriber]: '',
      [Context.Participant]: '',
    },
    [Model.Job]: {
      [Context.User]: 'Twoje oferty pracy',
      [Context.Subscriber]: 'Zapisane oferty pracy',
      [Context.Participant]: '',
    },
    [Model.Wiki]: {
      [Context.User]: 'Twoje artykuły',
      [Context.Subscriber]: 'Obserwowane artykuły',
      [Context.Participant]: 'Artykuły z Twoim udziałem'
    },
    [Model.Microblog]: {
      [Context.User]: '',
      [Context.Subscriber]: '',
      [Context.Participant]: ''
    }
  };

  const MODELS: ModelType = {
    [Model.Topic]: 'Wątki na forum',
    [Model.Job]: 'Oferty pracy',
    [Model.User]: 'Użytkownicy',
    [Model.Wiki]: 'Artykuły',
    [Model.Microblog]: 'Mikroblogi'
  };

  const Decorator = {
    props: {
      value: String,
      item: Object
    },
    methods: {
      highlight(text) {
        // @ts-ignore
        if (!this.value) {
          return text;
        }

        // @ts-ignore
        const ascii = this.value.normalize('NFD').replace(/[\u0300-\u036f]/g, "");
        // @ts-ignore
        const re = new RegExp(`\\b(${this.value}|${ascii})`, "i");

        return text.replace(re, "<strong>$1</strong>");
      }
    }
  };

  Vue.component('TopicDecorator', {
    mixins: [ Decorator ],
    template: '<a :href="item.url" class="text-truncate" tabindex="-1"><span v-html="highlight(item.subject, value)"></span> <small class="forum-name text-muted">w {{ item.forum.name }}</small></a>'
  });

  Vue.component('JobDecorator', {
    mixins: [ Decorator ],
    template: '<a :href="item.url" class="text-truncate" tabindex="-1"><span v-html="highlight(item.title, value)"></span></a>'
  });

  Vue.component('WikiDecorator', {
    mixins: [ Decorator ],
    template: '<a :href="item.url" class="text-truncate" tabindex="-1"><span v-html="highlight(item.title, value)"></span></a>'
  });

  Vue.component('UserDecorator', {
    mixins: [ Decorator ],
    components: { 'vue-avatar': VueAvatar },
    template:
      `<a :href="item.url" class="d-flex align-content-center text-truncate" tabindex="-1">
        <vue-avatar :photo="item.photo" class="i-16 mr-2"></vue-avatar> <span v-html="highlight(item.name, value)"></span>
        <div class="item-options">
          <a :href="item.url" class="ml-3" title="Przejdź do profilu użytkownika"><i class="fas fa-user"></i></a>
          <a :href="\'/User/Pm/Submit?to=\' + item.name" class="ml-3" title="Napisz wiadomość"><i class="fas fa-comment"></i></a>
          <a :href="\'/Forum/User/\' + item.id" class="ml-3" title="Znajdź posty użytkownika"><i class="fas fa-search"></i></a>
        </div>
      </a>`
  });

  @Component({
    name: 'app',
    mixins: [clickaway],
    store
  })
  export default class VueSearchbar extends Vue {
    isActive: boolean = false;
    isDropdownVisible: boolean = false;
    items: Hit[] = [];
    selectedIndex: number = -1;

    @Prop(String)
    readonly url: string = '';

    @Prop()
    value!: string;

    @Ref('input')
    readonly input!: HTMLInputElement;

    mounted() {
      document.addEventListener('keydown', this.shortcutSupport);
    }

    beforeDestroy() {
      document.removeEventListener('keydown', this.shortcutSupport);
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

    blurInput(): void {
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

    getCategoryLabel(category): string {
      return category.context !== undefined ? CONTEXTS[category.model][category.context] : MODELS[category.model];
    }

    completion(event): void {
      if (Object.values(SpecialKeys).includes(event.keyCode)) {
        return;
      }

      this.selectedIndex = -1; // reset position index after key pressed
      this.loadItems();
    }

    loadItems(): void {
      const endpoint = this.getEndpoint();
      const headers = this.$store.getters['user/isAuthorized'] ? {Authorization: `Bearer ${this.$store.state.user.token}`} : {};

      if (!endpoint) {
        return;
      }

      axios.get(endpoint, {params: {q: this.value || null}, headers}).then(response => {
        this.items = response.data;
        this.isDropdownVisible = true;
      });
    }

    getEndpoint(): string | null {
      return this.value.trim() === '' ? (this.$store.getters['user/isAuthorized'] ? '/completion/hub/' : null) : '/completion/';
    }

    shortcutSupport(event): void {
      if (event.key === SLASH && !/^(?:input|textarea|select|button)$/i.test(event.target.tagName)) {
        event.preventDefault();

        (this.$refs.input as HTMLInputElement).focus();
      }
    }

    changeUrl(): void {
      if (this.selectedIndex === -1) {
        (this.$refs.search as HTMLFormElement).submit();
      }

      window.location.href = this.items.find(item => item.index === this.selectedIndex)!.url;
    }

    makeDecorator(item: Hit): string {
      return `${item.model}Decorator`;
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
        category.children.map(child => Object.assign(child, { index: counter++ }));

        return category;
      });
    }
  }
</script>
