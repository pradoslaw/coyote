<template>
  <div v-on-clickaway="blurInput" class="nav-search">
    <div :class="{'search-bar-active': isActive}" class="search-bar">
      <i class="fas fa-search ml-2 mr-2"></i>

      <form :action="url" role="search" class="flex-grow-1">
        <input
          @focus="showDropdown"
          @keyup.esc="hideDropdown"
          v-model="value"
          type="text"
          name="q"
          autocomplete="off"
          placeholder="Kliknij, aby wyszukać"
        >
      </form>

      <div v-if="isDropdownVisible" class="search-dropdown">
<!--        <nav class="list-inline" style="margin: 10px 7px 10px 7px">-->
<!--          <a class="list-inline-item active mr-2 text-primary" href="#" style="padding: 5px; font-size: 90%; border-bottom: 2px solid #80a41a">Forum</a>-->
<!--          <a class="list-inline-item text-body" href="#" style="padding: 5px; font-size: 90%;">Praca</a>-->
<!--        </nav>-->

        <ul class="list-unstyled">
          <template v-for="(items, context) in context">
            <li class="title"><span>{{ context }}</span></li>

            <li v-for="item in items">
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

<!--          <li class="title"><span>Twoje dyskusje</span></li>-->

<!--          <li>-->
<!--            <a href="#">-->

<!--              <span>Lorem ipsum lores.</span>-->


<!--            </a>-->
<!--          </li>-->

<!--          <li class="title"><span>Ostatnie wyszukiwania</span></li>-->

<!--          <li>-->
<!--            <a href="#">-->

<!--              <span>Lorem ipsum lores.</span>-->


<!--            </a>-->
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
        type: String
      }
    },
    data() {
      return {
        isActive: false,
        isDropdownVisible: false,
        items: []
      }
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

      highlight(input) {
        const re = new RegExp(`^(${this.value})`, "i");

        return input.replace(re, "<strong>$1</strong>");
      },

      getContext(item) {
        if (item.context.startsWith('user:') && item.model === 'Topic') {
          return 'Twoje wątki';
        }
        else if (item.context.startsWith('users:') && item.model === 'Topic') {
          return 'Twoje dyskusje';
        }
        else if (item.context.startsWith('subscribers:')) {
          return 'Obserwowane';
        }

        return 'Wątki na forum';
      }
    },
    computed: {
      context() {
        return this.items.reduce((acc, item) => {
          const context = this.getContext(item);

          if (!acc[context]) {
            acc[context] = [];
          }

          acc[context].push(item);

          return acc;
        }, {});
      }
    },
    watch: {
      value: function(val) {
        // axios.get('/completion', {params: {q: val}}).then(response => console.log(response.data));
        axios.get('/completion', {params: {q: val}, headers: {Authorization: `Bearer ${this.$store.state.user.token}`}}).then(response => {
          this.items = response.data;
        });
      }
    }
  }
</script>
