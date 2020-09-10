<template>
  <div class="position-relative">
    <div ref="editor" class="form-control tag-editor">
      <ul ref="clouds" class="tag-clouds">
        <li v-for="tag in tags"><a @click="toggleTag(tag)" class="remove">{{ tag.name }}</a></li>
      </ul>

      <input
        v-model="inputText"
        :style="`width: ${inputWidth}`"
        @keyup.space="setTag"
        @keyup.enter="setTag"
        ref="input"
        type="text"
        tabindex="4"
        placeholder="Np. c#, .net"
        autocomplete="off"
      >
    </div>

    <vue-dropdown :items="filteredTags" @select="toggleTag" class="tag-dropdown">
      <template v-slot:item="slot">
        <span>{{ slot.item.name }}</span>
        <small>×{{ slot.item.count }}</small>
      </template>
    </vue-dropdown>
  </div>
</template>

<script lang="ts">
  import Vue from "vue";
  import { Prop, Watch } from "vue-property-decorator";
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
    sourceUrl!: string;

    @Prop({default: () => []})
    tags!: Tag[];

    private inputText: string = '';
    private filteredTags = [];
    private inputWidth = '100%';

    @Watch('inputText')
    onInputTextChanged(newVal) {
      if (!newVal) {
        return;
      }

      axios.get(this.sourceUrl, {params: {q: newVal}}).then(result => this.filteredTags = result.data);
    }

    toggleTag(tag: Tag) {
      this.inputText = '';
      (this.$refs.input as HTMLInputElement).focus();



      this.$emit('change', tag);
      this.$nextTick(() => this.calcInputWidth());
    }

    setTag() {
      let name = this.inputText.trim().toLowerCase().replace(/[^a-ząęśżźćółń0-9\-\.#\+\s]/gi, '')

      if (name.startsWith('#')) {
        name = name.substr(1);
      }

      this.toggleTag({ name })
    }

    private calcInputWidth() {
      const editor = (this.$refs.editor as HTMLElement);
      const styles = window.getComputedStyle(editor);

      this.inputWidth = (editor.offsetWidth - (this.$refs.clouds as HTMLElement).offsetWidth - parseInt(styles.paddingLeft) - 1) + 'px';
    }
  }
</script>
