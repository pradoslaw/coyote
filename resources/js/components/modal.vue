<template>
  <div tabindex="-1"
       class="modal fade background-darken"
       role="dialog"
       @click.self="close()"
       @keyup.esc="close()"
       v-show="isOpen"
       :class="{'d-block': isOpen, 'show': isOpen}"
       ref="container"
  >
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">
            <slot name="title">
              Błąd
            </slot>
          </h4>
        </div>

        <div class="modal-body" v-if="$slots.default">
          <slot></slot>
        </div>

        <div class="modal-footer">
          <slot name="buttons">
            <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="close()">OK</button>
          </slot>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import {nextTick} from "../vue";

export default {
  name: 'VueModal',
  data() {
    return {
      isOpen: false,
      bodyOverflow: '',
    };
  },
  methods: {
    open() {
      this.isOpen = true;
      nextTick(() => {
        this.$refs['container'].focus(); // firefox hack: set focus to make Esc button works
        this.bodyOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
      });
    },
    close() {
      this.isOpen = false;
      document.body.style.overflow = this.bodyOverflow;
    },
  },
};
</script>
