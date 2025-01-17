<template>
  <div ref="editor" class="tag-editor dropup">
    <span class="align-self-center mx-2 text-muted">
      <vue-icon name="tag"/>
    </span>
    <ul ref="cloud" class="tag-clouds">
      <li v-for="tag in popularTags.slice(0, 3)">
        <a @click="toggleTag({ name: tag.name })" class="suggest" :aria-label="tag.text" data-balloon-pos="up">
          <vue-icon name="tagPopularInclude"/>
          {{ tag.name }}
        </a>
      </li>
    </ul>

    <template v-if="popularTags.length > 3">
      <button class="btn btn-xs text-muted" type="button" title="Więcej...">
        <vue-icon name="tagPopularMore"/>
      </button>

      <div class="dropdown-menu p-2">
        <ul class="tag-clouds">
          <li v-for="tag in popularTags.slice(3)">
            <a @click="toggleTag({name: tag.name})" class="suggest" :aria-label="tag.text" data-balloon-pos="up">
              <vue-icon name="tagPopularInclude"/>
              {{ tag.name }}
            </a>
          </li>
        </ul>
      </div>
    </template>

    <ul class="tag-clouds">
      <li v-for="tag in tags">
        <span class="neon-tag">
          {{ tag.name }}
          <a @click="toggleTag(tag)" class="remove">
            <vue-icon name="tagRemove"/>
          </a>
        </span>
      </li>
    </ul>

    <input
      :value="searchText"
      :placeholder="placeholder"
      @input="searchText = $event.target.value"
      @keyup.space="setTag"
      @keydown.enter="selectTag"
      @keyup.esc="dropdown.hideDropdown"
      @keyup.up.prevent="dropdown.goUp"
      @keyup.down.prevent="dropdown.goDown"
      ref="input"
      type="text"
      tabindex="4"
      autocomplete="off"
      name="tags"
    >

    <vue-dropdown :items="filteredTags" @select="toggleTag" ref="dropdown" class="tag-dropdown mt-2 w-100">
      <template v-slot:item="slot">
        <span class="neon-tag" v-text="slot.item.name"/>
        <small>×{{ slot.item.topics + slot.item.microblogs + slot.item.jobs }}</small>
      </template>
    </vue-dropdown>
  </div>
</template>

<script lang="ts">
import axios, {AxiosResponse} from 'axios';

import {Tag} from '../../types/models';
import VueDropdown from '../forms/dropdown.vue';
import VueIcon from "../icon";

export default {
  name: 'tags-inline',
  components: {
    VueIcon,
    'vue-dropdown': VueDropdown,
  },
  emits: ['change'],
  props: {
    sourceUrl: {
      type: String,
      default: '/completion/prompt/tags',
    },
    placeholder: {
      type: String,
      default: '',
    },
    tags: {
      type: Array,
      default: () => [],
    },
    popularTags: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      searchText: '',
      filteredTags: [],
    };
  },
  watch: {
    searchText(val) {
      if (!val) {
        return;
      }
      axios.get<any>(this.sourceUrl, {params: {q: val}})
        .then((result: AxiosResponse<any>) => this.filteredTags = result.data);
    },
  },
  methods: {
    toggleTag(tag: Tag) {
      this.searchText = '';
      this.$refs.input.focus();

      this.$emit('change', tag);

      const searchIndex = this.popularTags.findIndex(el => el.name === tag.name);

      if (searchIndex > -1) {
        this.popularTags.splice(searchIndex, 1);
      }
    },
    selectTag(event) {
      if (this.$refs.dropdown.getSelected()) {
        this.applyTag(this.$refs.dropdown.getSelected().name);
        event.preventDefault(); // prevent submitting the form
      }
    },
    setTag() {
      let input = this.searchText.trim().toLowerCase().replace(/[^a-ząęśżźćółń0-9\-\.#\+\s]/gi, '');

      if (input.startsWith('#')) {
        input = input.substring(1);
      }

      if (input) {
        this.applyTag(input);
      }
    },
    applyTag(name: string) {
      this.toggleTag({name});
      this.$refs.dropdown.hideDropdown();
    },
  },
};
</script>
