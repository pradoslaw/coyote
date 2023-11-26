<template>
  <div class="emoji-picker" v-on-clickaway="blur">
    <div class="card card-body">
      <div style="display:flex; flex-direction:column;">
        <div class="emoji-grid">
          <div class="emoji-scroll" @mouseleave="mouseLeave">
            <div class="category" v-for="category in emojis.categories">
              <h5>{{ category.name }}</h5>
              <div class="category-emojis">
                <template v-for="name in category.subcategories">
                  <img
                    v-for="code in emojis.subcategories[name]"
                    v-show="visible(emojis.emoticons[code])"
                    class="emoji"
                    :src="url(emojis.emoticons[code])"
                    :title="emojis.emoticons[code].name"
                    :alt="emojis.emoticons[code].native"
                    @mouseover="mouseOver(emojis.emoticons[code])"
                    @click="select(code)"/>
                </template>
              </div>
            </div>
          </div>

          <div class="emoji-preview-box">
            <div v-show="emoji" class="emoji-preview">
              <div class="emoji-name">
                <p class="title">{{ emoji && emoji.name }}</p>
                <code class="id">:{{ emoji && emoji.id }}:</code>
              </div>
              <small>{{ keywords(emoji) }}</small>
            </div>
            <p v-show="!emoji" class="emoji-placeholder">
              Wybierz emoji
            </p>
          </div>
        </div>

        <div class="search-box">
          <input class="form-control" v-model="searchPhrase" placeholder="Wyszukaj emoji..."/>
          <button class="btn btn-primary" type="button" ref="close-button" @click="close">
            <i class="fas fa-times"/>
            Zamknij
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {mixin as clickAway} from 'vue-clickaway';

export default {
  mixins: [clickAway],
  props: {
    emojis: {require: true},
  },
  data() {
    return {
      searchPhrase: '',
      emoji: null,
    };
  },
  methods: {
    select(emoji) {
      this.$emit('input', emoji);
    },
    blur(blurEvent) {
      if (this.isDesktop()) {
        this.$emit('blur', blurEvent);
      }
    },
    close(closeEvent) {
      this.$emit('close', closeEvent);
    },
    url(emoji) {
      return 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/svg/' + emoji.unified + '.svg';
    },
    title(emoji) {
      return `:${emoji.id}:, ${emoji.name}`;
    },
    visible(emoji) {
      if (containsWords(emoji.id, this.searchSlug)) {
        return true;
      }
      for (const keyword of emoji.keywords) {
        if (containsWords(keyword, this.searchSlug)) {
          return true;
        }
      }
      return false;
    },
    clearSearchPhrase() {
      this.searchPhrase = '';
    },
    keywords(emoji) {
      if (emoji) {
        return emoji.keywords.map(keyword => '"' + keyword + '"').join(", ");
      }
      return '';
    },
    mouseOver(emoji) {
      this.emoji = emoji;
    },
    mouseLeave() {
      this.emoji = null;
    },
    isDesktop() {
      const closeButton = this.$refs["close-button"];
      if (closeButton) {
        const style = window.getComputedStyle(closeButton);
        return style.display === 'none';
      }
      return false;
    }
  },
  computed: {
    searchSlug() {
      return this.searchPhrase.replaceAll(/[^a-z0-9]/gi, '');
    }
  }
};

function containsWords(subject, searchPhrase) {
  return subject.replaceAll(/[-_]/g, '').includes(searchPhrase);
}
</script>
