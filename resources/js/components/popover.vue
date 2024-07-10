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
import Session from '../libs/session.js';

type Placement = 'top' | 'bottom' | 'left' | 'right' | 'top-start';

export default Vue.extend({
  name: 'Popover',
  props: {
    message: {
      type: String,
      required: true,
    },
    placement: {
      type: String,
      default: 'bottom',
    },
  },
  methods: {
    closeMessage() {
      let popover = JSON.parse(Session.getItem('popover', '[]'));
      popover.push(this.message);

      Session.setItem('popover', JSON.stringify(popover));

      this.$destroy();
      this.$el.parentNode!.removeChild(this.$el);
    },
  },
});
</script>
