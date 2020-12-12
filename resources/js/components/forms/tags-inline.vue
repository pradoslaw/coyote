<template>
  <div class="position-relative">
    <div ref="editor" class="form-control tag-editor">
      <ul ref="cloud" class="tag-clouds">
        <li v-for="tag in tags"><a @click="toggleTag(tag)" class="remove">{{ tag.name }}</a></li>
      </ul>

      <input
        v-model="searchText"
        :style="`width: ${inputWidth}`"
        :placeholder="placeholder"
        @keyup.space="applyTag"
        @keyup.enter.prevent="applyTag"
        @keyup.esc="dropdown.toggleDropdown(false)"
        @keyup.up.prevent="dropdown.goUp"
        @keyup.down.prevent="dropdown.goDown"
        ref="input"
        type="text"
        tabindex="4"
        autocomplete="off"
        name="tags"
      >
    </div>

    <vue-dropdown :items="filteredTags" @select="toggleTag" :default-index="-1" ref="dropdown" class="tag-dropdown">
      <template v-slot:item="slot">
        <span>{{ slot.item.name }}</span>
        <small>×{{ slot.item.topics }}</small>
      </template>
    </vue-dropdown>
  </div>
</template>

<script lang="ts">
  import Vue from "vue";
  import { Prop, Watch, Ref } from "vue-property-decorator";
  import Component from "vue-class-component";
  import VueDropdown from '../forms/dropdown.vue';
  import { Tag } from '../../types/models';
  import axios from 'axios';

  @Component({
    name: 'tags-inline',
    components: {
      'vue-dropdown': VueDropdown
    },
  })
  export default class VueTagsInline extends Vue {
    @Prop({default: '/Forum/Tag/Prompt'})
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

    private searchText: string = '';
    private filteredTags = [];
    private inputWidth = '100%';

    @Watch('searchText')
    searchResults(newVal) {
      if (!newVal) {
        return;
      }

      axios.get(this.sourceUrl, {params: {q: newVal}}).then(result => this.filteredTags = result.data);
    }

    toggleTag(tag: Tag) {
      this.searchText = '';
      this.input.focus();

      this.$emit('change', tag);
      this.$nextTick(() => this.calcInputWidth());
    }

    applyTag() {
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
