<template>
  <div ref="modal" class="modal fade background-darken" tabindex="-1" role="dialog" @click.self="close()" @keyup.esc="close()" v-show="isOpen" :class="{'d-block': isOpen, 'show': isOpen}">
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
import Vue from 'vue';
import Component from "vue-class-component";
import { Ref } from 'vue-property-decorator';

@Component
export default class VueModal extends Vue {
  isOpen = false;

  @Ref()
  private modal!: HTMLElement;
  private bodyOverflow: string = '';

  open() {
    this.isOpen = true;

    this.$nextTick(() => {
      this.modal.focus();

      this.bodyOverflow = document.body.style.overflow;
      document.body.style.overflow = 'hidden';
    })
  }

  close() {
    this.isOpen = false;
    document.body.style.overflow = this.bodyOverflow;
  }
}
</script>
