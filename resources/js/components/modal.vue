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

<script>
  export default {
    props: ['title'],
    data: function () {
      return {
        isOpen: false,
        _bodyOverflow: ''
      }
    },
    methods: {
      open() {
        this.isOpen = true;

        this.$nextTick(() => {
          this.$refs.modal.focus();

          this._bodyOverflow = document.body.style.overflow;
          document.body.style.overflow = 'hidden';
        })
      },

      close() {
        this.isOpen = false;
        document.body.style.overflow = this._bodyOverflow;
      }
    }
  }
</script>
