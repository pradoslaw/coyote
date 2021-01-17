<template>
  <div ref="editor" class="tag-editor">
    <ul ref="cloud" class="tag-clouds">
      <li v-for="tag in popularTags.slice(0, 3)">
        <a @click="toggleTag({ name: tag })" class="suggest" :title="`Dodaj tag '${tag}'`">
          <i class="fa fa-plus"></i>

          {{ tag }}
        </a>
      </li>
    </ul>

    <template v-if="popularTags.length > 3">
      <button class="btn btn-xs text-muted" type="button" data-toggle="dropdown" aria-label="Dropdown"><i class="fa fa-ellipsis-h"></i></button>

      <div class="dropdown-menu p-2">
        <ul class="tag-clouds">
          <li v-for="tag in popularTags.slice(3)">
            <a @click="toggleTag({ name: tag })" class="suggest" :title="`Dodaj tag '${tag}'`">
              <i class="fa fa-plus"></i>

              {{ tag }}
            </a>
          </li>
        </ul>
      </div>
    </template>

    <ul class="tag-clouds">
      <li v-for="tag in tags">
        <span>
          {{ tag.name }}

          <a @click="toggleTag(tag)" class="remove"><i class="fa fa-times"></i></a>
        </span>
      </li>
    </ul>

    <input
      v-model="searchText"
      :placeholder="placeholder"
      @keyup.space="setTag"
      @keyup.enter.prevent="selectTag"
      @keyup.esc="dropdown.hideDropdown"
      @keyup.up.prevent="dropdown.goUp"
      @keyup.down.prevent="dropdown.goDown"
      ref="input"
      type="text"
      tabindex="4"
      autocomplete="off"
      name="tags"
    >

    <vue-dropdown :items="filteredTags" @select="toggleTag" ref="dropdown" class="tag-dropdown mt-2">
      <template v-slot:item="slot">
        <span>{{ slot.item.name }}</span>
        <small>×{{ slot.item.topics }}</small>
      </template>
    </vue-dropdown>
  </div>
</template>

<script lang="ts">
  import Vue from "vue";
  import {Prop, Watch, Ref, InjectReactive} from "vue-property-decorator";
  import Component from "vue-class-component";
  import VueDropdown from '../forms/dropdown.vue';
  import { Tag } from '@/types/models';
  import axios from 'axios';

  @Component({
    name: 'tags-inline',
    components: {
      'vue-dropdown': VueDropdown
    },
  })
  export default class VueTagsInline extends Vue {
    @Prop({default: '/completion/prompt/tags'})
    readonly sourceUrl!: string;

    @Prop({default: ''})
    readonly placeholder!: string;

    @Prop({default: () => []})
    readonly tags!: Tag[];

    @Ref('dropdown')
    readonly dropdown!: VueDropdown;

    @Ref('input')
    readonly input!: HTMLInputElement;

    @Ref('editor')
    readonly editor!: HTMLElement;

    @Ref('cloud')
    readonly cloud!: HTMLElement;

    @InjectReactive({from: 'popularTags', default: []})
    readonly popularTags!: string[];

    private searchText: string = '';
    private filteredTags = [];

    @Watch('searchText')
    filterResults(searchText) {
      if (!searchText) {
        return;
      }

      axios.get(this.sourceUrl, { params: {q: searchText} }).then(result => this.filteredTags = result.data);
    }

    toggleTag(tag: Tag) {
      this.searchText = '';
      this.input.focus();

      this.$emit('change', tag);

      const searchIndex = this.popularTags.indexOf(tag.name);

      if (searchIndex > -1) {
        this.popularTags.splice(searchIndex, 1);
      }
    }

    selectTag() {
      // @ts-ignore
      if (this.dropdown.getSelected()) {
        // @ts-ignore
        this.applyTag(this.dropdown.getSelected()['name']);

        return false; // prevent submitting the form
      }
    }

    setTag() {
      let input = this.searchText.trim().toLowerCase().replace(/[^a-ząęśżźćółń0-9\-\.#\+\s]/gi, '')

      if (input.startsWith('#')) {
        input = input.substr(1);
      }

      if (input) {
        this.applyTag(input);
      }
    }

    private applyTag(name: string) {
      this.toggleTag({ name });
      // @ts-ignore
      // hiding dropdown resets internal index of selected position. it's important because otherwise pressing enter would apply
      // last selected tag
      this.dropdown.hideDropdown();
    }
  }
</script>
