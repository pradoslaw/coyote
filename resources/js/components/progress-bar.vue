<template>
  <div class="progress-bar-dotted">
    <span
      v-for="i in max"
      :aria-label="tooltips[i - 1]"
      :class="{'editable': editable}"
      @click="setValue(i)"
      data-balloon-pos="down"
    >
      <i class="fas fa-circle" :class="{'text-primary': valueLocal >= i, 'text-muted': valueLocal < i}"></i>
    </span>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import { Prop } from "vue-property-decorator";
  import { default as mixin } from '@/components/mixins/form.js';

  @Component({
    mixins: [ mixin ]
  })
  export default class VueProgressBar extends Vue {
    @Prop()
    readonly value!: number;

    @Prop({default: false})
    readonly editable!: boolean;

    @Prop({default: 3})
    readonly max!: number;

    @Prop({default: () => ['podstawy', 'średnio zaawansowany', 'zaawansowany']})
    readonly tooltips!: string[];

    setValue(value: number) {
      if (!this.editable) {
        return;
      }

      this.$emit('click', value);
    }
  }
</script>
