<template>
  <div class="emoji-picker">
    <div class="card card-body">
      <div class="panel">
        <input class="form-control" placeholder="Wyszukaj emoji..." v-model="searchPhrase"/>
      </div>

      <div class="scroll" @mouseleave="mouseLeave">
        <div class="category" v-for="category in emojis.categories">
          <h5>{{ category.name }}</h5>
          <template v-for="name in category.subcategories">
            <img
                v-for="code in emojis.subcategories[name]"
                v-show="visible(emojis.emoticons[code])"
                class="emoji"
                :src="url(emojis.emoticons[code])"
                :title="emojis.emoticons[code].name"
                :alt="emojis.emoticons[code].native"
                @mouseover="mouseOver(emojis.emoticons[code])"/>
          </template>
        </div>
      </div>

      <div class="panel">
        <div class="footer">
          <div v-show="emoji">
            <div class="emoji-details">
              <p class="name">{{ emoji && emoji.name }}</p>
              <code class="id">:{{ emoji && emoji.id }}:</code>
            </div>
            <small>{{ keywords(emoji) }}</small>
          </div>
          <p v-show="!emoji" class="placeholder">Wybierz emoji</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
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
    url(emoji) {
      return 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/svg/' + emoji.unified + '.svg';
    },
    title(emoji) {
      return `:${emoji.id}:, ${emoji.name}`;
    },
    visible(emoji) {
      if (emoji.id.includes(this.searchPhrase)) {
        return true;
      }
      for (const keyword of emoji.keywords) {
        if (keyword.includes(this.searchPhrase)) {
          return true;
        }
      }
      return false;
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
    }
  }
};
</script>
