<template>
  <div v-on-clickaway="blurInput" class="nav-search">
    <div :class="{'search-bar-active': isActive}" class="search-bar">
      <i class="fas fa-search ml-2 mr-2"></i>

      <form :action="url" role="search" class="flex-grow-1">
        <input
          ref="input"
          @focus="showDropdown"
          @keyup.esc="hideDropdown"
          @keyup="completion"
          @keyup.up.prevent="up"
          @keyup.down.prevent="down"
          v-model="value"
          type="text"
          name="q"
          autocomplete="off"
          placeholder="Kliknij, aby wyszukać"
        >
      </form>

      <div v-if="isDropdownVisible && this.items.length > 0" class="search-dropdown">
<!--        <nav class="list-inline" style="margin: 10px 7px 10px 7px">-->
<!--          <a class="list-inline-item active mr-2 text-primary" href="#" style="padding: 5px; font-size: 90%; border-bottom: 2px solid #80a41a">Forum</a>-->
<!--          <a class="list-inline-item text-body" href="#" style="padding: 5px; font-size: 90%;">Praca</a>-->
<!--        </nav>-->

        <ul class="list-unstyled">
          <template v-for="(items, context) in context">
            <li class="title"><span>{{ context }}</span></li>

            <li v-for="item in items" :class="{'hover': item.index === selectedIndex}">
              <a :href="item.url">
                <span v-html="highlight(item.subject)"></span> <small style="font-size: .65rem" class="text-muted">w {{ item.forum.name }}</small>
              </a>
            </li>
          </template>


<!--          <li class="hover position-relative">-->
<!--            <a href="#">-->

<!--              <span>Które firmy IT dotknął kryzys? Sprawdź tutaj.</span> <small style="font-size: .65rem" class="text-muted">w Forum » Off-Topic</small>-->



<!--            </a>-->

<!--            <div class="position-absolute" style="right: 10px;  top: 20%;">-->
<!--              <i class="far fa-star ml-2"></i>-->
<!--              <i class="fas fa-search ml-2"></i>-->
<!--            </div>-->
<!--          </li>-->

<!--          <li>-->
<!--            <a href="#">-->

<!--              <span>Lorem ipsum lores.</span>-->
<!--              <small style="font-size: .65rem" class="text-muted">w Forum » Off-Topic</small>-->

<!--            </a>-->
<!--          </li>-->

<!--          <li class="more">-->
<!--            <a href="#">więcej ...</a>-->
<!--          </li>-->



<!--          <li class="title"><span>Użytkownicy</span></li>-->

<!--          <li class="position-relative">-->
<!--            <a href="#" class="d-flex align-content-center">-->
<!--              <object data="https://4programmers.net/uploads/photo/4ccbfa1158d19" style="width: 16px; height: 16px" type="image/png" class="media-object mr-2"><img src="/img/avatar.png"></object>-->
<!--              <span>Marooned</span>-->


<!--            </a>-->

<!--            <div class="position-absolute" style="right: 10px;  top: 20%;">-->
<!--              <i class="far fa-user ml-2"></i>-->
<!--              <i class="far fa-comment-alt ml-2"></i>-->
<!--              <i class="fas fa-search ml-2"></i>-->
<!--            </div>-->
<!--          </li>-->
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
  import { mixin as clickaway } from 'vue-clickaway';
  import axios from 'axios';
  import store from '../../store';

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
        selectedIndex: -1
      }
    },
    mounted() {
      document.addEventListener('keydown', this.keyboardSupport);
    },
    beforeDestroy() {
      document.removeEventListener('keydown', this.keyboardSupport);
    },
    methods: {
      showDropdown() {
        this.isActive = true;
        this.isDropdownVisible = true;
      },

      hideDropdown() {
        this.isDropdownVisible = false;
      },

      blurInput() {
        this.isActive = false;
      },

      down() {
        this.isDropdownShown = true;

        this.changeIndex(++this.selectedIndex);
      },

      up() {
        this.changeIndex(--this.selectedIndex);
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

      highlight(input) {
        const re = new RegExp(`^(${this.value})`, "i");

        return input.replace(re, "<strong>$1</strong>");
      },

      getContext(item) {
        if (item.context.startsWith('user:') && item.model === 'Topic') {
          return 'Twoje wątki';
        }
        else if (item.context.startsWith('participant:') && item.model === 'Topic') {
          return 'Twoje dyskusje';
        }
        else if (item.context.startsWith('subscriber:')) {
          return 'Obserwowane';
        }

        return 'Wątki na forum';
      },

      completion() {
        if (this.value === undefined || this.value.trim() === '') {
          return;
        }

        axios.get('/completion', {params: {q: this.value}, headers: {Authorization: `Bearer ${this.$store.state.user.token}`}}).then(response => {
          this.items = response.data;
        });
      },

      keyboardSupport(e) {
        if (e.keyCode === 32 && (e.ctrlKey || e.metaKey)) {
          e.preventDefault();

          this.showDropdown();
          this.$refs.input.focus();
        }
      }
    },
    computed: {
      context() {
        return this.items.reduce((acc, item, index) => {
          const context = this.getContext(item);

          if (!acc[context]) {
            acc[context] = [];
          }

          acc[context].push(Object.assign(item, { index }));

          return acc;
        }, {});
      }
    }
  }
</script>
