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
          placeholder="Kliknij, aby wyszukać (Ctrl+spacja)"
        >
      </form>

      <div v-if="isDropdownVisible && items.length > 0" class="search-dropdown">
<!--      <div v-if="isDropdownVisible && items.length > 0" class="search-dropdown">-->
<!--        <nav class="list-inline" style="margin: 10px 7px 10px 7px">-->
<!--          <a class="list-inline-item active mr-2 text-primary" href="#" style="padding: 5px; font-size: 90%; border-bottom: 2px solid #80a41a">Forum</a>-->
<!--          <a class="list-inline-item text-body" href="#" style="padding: 5px; font-size: 90%;">Praca</a>-->
<!--        </nav>-->

        <ul class="list-unstyled">
          <template v-for="category in categories">
            <li class="title"><span>{{ getCategoryLabel(category) }}</span></li>

            <li v-for="child in category.children" :class="{'hover': child.index === selectedIndex}" @mouseover="hoverItem(child.index)">
              <component :is="makeDecorator(child)" :item="child" :value="value"></component>
            </li>

<!--            <li v-if="contexts.length > 0" class="more">-->
<!--              <a href="#">więcej ...</a>-->
<!--            </li>-->
          </template>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
  import { mixin as clickaway } from 'vue-clickaway';
  import axios from 'axios';
  import store from '../../store';
  import VueAvatar from '../avatar.vue';

  const ESC = 27;
  const ENTER = 13;
  const UP = 38;
  const DOWN = 40;
  const RIGHT = 49;
  const LEFT = 37;
  const SHIFT = 16;
  const TAB = 9;
  const CTRL = 17;
  const ALT = 18;

  const MODELS = {
    'Topic': 'Wątki na forum',
    'Job': 'Oferty pracy',
    'User': 'Użytkownicy',
    'Wiki': 'Artykuły'
  };

  const CONTEXTS = {
    'Topic': {
      'user': 'Twoje wątki',
      'subscriber': 'Obserwowane wątki',
      'participant': 'Twoje dyskusje',
    },
    'Job': {
      'user': 'Twoje oferty pracy',
      'subscriber': 'Zapisane oferty pracy',
    },
    'Wiki': {
      'user': 'Twoje artykuły',
      'subscriber': 'Obserwowane artykuły',
      'participant': 'Artykuły z Twoim udziałem'
    }
  };

  const decorator = {
    props: {
      value: {
        type: String,
      },
      item: {
        type: Object
      }
    },
    methods: {
      highlight(text) {
        if (!this.value) {
          return text;
        }

        const ascii = this.value.normalize('NFD').replace(/[\u0300-\u036f]/g, "");
        const re = new RegExp(`\\b(${this.value}|${ascii})`, "i");

        return text.replace(re, "<strong>$1</strong>");
      }
    }
  };

  const decorators = [
    {
      name: "Topic",
      component: {
        mixins: [ decorator ],
        template: '<a :href="item.url" class="text-truncate"><span v-html="highlight(item.subject, value)"></span> <small class="forum-name text-muted">w {{ item.forum.name }}</small></a>'
      }
    },
    {
      name: "Job",
      component: {
        mixins: [ decorator ],
        template: '<a :href="item.url" class="text-truncate"><span v-html="highlight(item.title, value)"></span></a>'
      }
    },
    {
      name: "Wiki",
      component: {
        mixins: [ decorator ],
        template: '<a :href="item.url" class="text-truncate"><span v-html="highlight(item.title, value)"></span></a>'
      }
    },
    {
      name: "User",
      component: {
        mixins: [ decorator ],
        components: { 'vue-avatar': VueAvatar },
        template:
          '<a :href="item.url" class="d-flex align-content-center text-truncate">' +
            '<vue-avatar :photo="item.photo" class="i-16 mr-2"></vue-avatar> <span v-html="highlight(item.name, value)"></span>' +
            '<div class="item-options">' +
              '<a :href="item.url" class="ml-3" title="Przejdź do profilu użytkownika"><i class="fas fa-user"></i></a>' +
              '<a :href="\'/User/Pm/Submit?to=\' + item.name" class="ml-3" title="Napisz wiadomość"><i class="fas fa-comment"></i></a>' +
              '<a :href="\'/Forum/User/\' + item.id" class="ml-3" title="Znajdź posty użytkownika"><i class="fas fa-search"></i></a>' +
            '</div>' +
          '</a>'
      }
    }
  ];

  export default {
    mixins: [clickaway],
    store,
    props: {
      url: {
        type: String,
        required: true
      },
      value: {
        type: String,
        default: ''
      }
    },
    data() {
      return {
        isActive: false,
        isDropdownVisible: false,
        items: [],
        selectedIndex: -1,
        decorators
      }
    },
    mounted() {
      document.addEventListener('keydown', this.shortcutSupport);
    },
    beforeDestroy() {
      document.removeEventListener('keydown', this.shortcutSupport);
    },
    methods: {
      showDropdown() {
        this.isActive = true;
        this.isDropdownVisible = true;

        // show basic set of links for given user even if no query was provided
        this.getItems();
      },

      hideDropdown() {
        this.isDropdownVisible = false;
        this.value = '';
      },

      blurInput() {
        this.isActive = false;
      },

      clearInput() {
        this.value = '';
        this.getItems();
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
          }
          else if (index < 0) {
            index = length - 1;
          }

          this.selectedIndex = index;
        }
      },

      getCategoryLabel(category) {
        return category.context !== undefined ? CONTEXTS[category.model][category.context] : MODELS[category.model];
      },

      completion(event) {
        if ([UP, DOWN, ESC, ENTER, SHIFT, TAB, CTRL, ALT, LEFT, RIGHT].includes(event.keyCode)) {
          return;
        }

        this.selectedIndex = -1; // reset position index after key pressed
        this.getItems();
      },

      getItems() {
        const endpoint = this.getEndpoint();
        let headers = this.$store.getters['user/isAuthorized'] ? {Authorization: `Bearer ${this.$store.state.user.token}`} : {};

        if (!endpoint) {
          return;
        }

        axios.get(endpoint, {params: {q: this.value || null}, headers}).then(response => {
          this.items = response.data;
          this.isDropdownVisible = true;
        });
      },

      getEndpoint() {
        return this.value === undefined || this.value.trim() === '' ? (this.$store.getters['user/isAuthorized'] ? '/completion/hub/' : null) : '/completion/';
      },

      shortcutSupport(event) {
        if (event.keyCode === 32 && (event.ctrlKey || event.metaKey)) {
          event.preventDefault();

          this.$refs.input.focus();
        }
      },

      changeUrl() {
        if (this.selectedIndex === -1) {
          this.$refs.search.submit();
        }

        window.location.href = this.items.find(item => item.index === this.selectedIndex).url;
      },

      makeDecorator(item) {
        return this.decorators.find(decorator => decorator.name === item.model).component;
      }
    },
    computed: {
      categories() {
        let counter = 0;
        let result = {};

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
  }
</script>
