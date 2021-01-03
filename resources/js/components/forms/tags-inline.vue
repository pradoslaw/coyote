<template>
  <div ref="editor" class="tag-editor">
    <ul ref="cloud" class="tag-clouds">
      <li v-for="tag in popularTags">
        <a @click="toggleTag({ name: tag })" class="suggest" :title="`Dodaj tag '${tag}'`">
          <i class="fa fa-plus"></i>

          {{ tag }}
        </a>
      </li>

      <li v-for="tag in tags">
        <span>
          {{ tag.name }}

          <a @click="toggleTag(tag)" class="remove"><i class="fa fa-times"></i></a>
        </span>
      </li>
    </ul>

    <input
      v-model="searchText"
      :style="`width: ${inputWidth}`"
      :placeholder="placeholder"
      @keyup.space="validateTag"
      @keyup.enter.prevent="validateTag"
      @keyup.esc="dropdown.toggleDropdown(false)"
      @keyup.up.prevent="dropdown.goUp"
      @keyup.down.prevent="dropdown.goDown"
      ref="input"
      type="text"
      tabindex="4"
      autocomplete="off"
      name="tags"
    >

    <vue-dropdown :items="filteredTags" @select="toggleTag" :default-index="-1" ref="dropdown" class="tag-dropdown mt-2">
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

    @InjectReactive()
    readonly popularTags!: string[];

    private searchText: string = '';
    private filteredTags = [];
    private inputWidth = '100%';

    @Watch('searchText')
    filterResults(searchText) {
      if (!searchText) {
        return;
      }

      axios.get(this.sourceUrl, { params: {q: searchText} }).then(result => this.filteredTags = result.data);
    }

    mounted() {
      this.calcInputWidth();
    }

    toggleTag(tag: Tag) {
      this.searchText = '';
      this.input.focus();

      this.$emit('change', tag);
      this.$nextTick(() => this.calcInputWidth());

      const searchIndex = this.popularTags.indexOf(tag.name);

      if (searchIndex > -1) {
        this.popularTags.splice(searchIndex, 1);
      }
    }

    async validateTag() {
      const filterValue = () => {
        let input = this.searchText.trim().toLowerCase().replace(/[^a-ząęśżźćółń0-9\-\.#\+\s]/gi, '')

        if (input.startsWith('#')) {
          input = name.substr(1);
        }

        return input;
      }

      // @ts-ignore
      const name = this.dropdown.getSelected() ? this.dropdown.getSelected()['name'] : filterValue();

      if (!name) {
        return;
      }

      this.applyTag(name);
    }

    applyTag(name: string) {
      this.toggleTag({ name });
      // @ts-ignore
      this.dropdown.toggleDropdown(false);
    }

    private calcInputWidth() {
      const styles = window.getComputedStyle(this.editor);

      this.inputWidth = (this.editor.offsetWidth - this.cloud.offsetWidth - parseInt(styles.paddingLeft) - 1) + 'px';
    }
  }
</script>
