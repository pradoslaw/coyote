<template>
  <div style="z-index: 1">
    <transition name="fade">
      <div :class="placement" class="alert alert-warning alert-dismissible mb-0">
        <button @click="closeMessage" type="button" class="close" data-dismiss="alert" aria-label="Close" title="Kliknij, aby zamknąć">
          <span aria-hidden="true">&times;</span>
        </button>

        {{ message }}
      </div>
    </transition>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import { Prop } from "vue-property-decorator";
  import Session from '../libs/session';

  type Placement = 'top' | 'bottom' | 'left' | 'right' | 'top-start';

  @Component
  export default class Popover extends Vue {
    @Prop()
    readonly message!: string;

    @Prop({default: 'bottom'})
    readonly placement!: Placement;

    closeMessage() {
      let popover = JSON.parse(Session.getItem('popover', '[]'));
      popover.push(this.message);

      Session.setItem('popover', JSON.stringify(popover));

      // destroy the vue listeners, etc
      this.$destroy();

      // remove the element from the DOM
      this.$el.parentNode!.removeChild(this.$el);
    }
  }
</script>
