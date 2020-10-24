<template>
  <div>
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

type Placement = 'top' | 'bottom' | 'left' | 'right';

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

<style lang="scss">

@import "@/sass/helpers/_variables.scss";
@import "~bootstrap/scss/functions";
@import "~bootstrap/scss/variables";
@import "~bootstrap/scss/mixins/breakpoints";

.alert-dismissible .close {
  padding: .16rem .25rem !important;
}

.alert.left, .alert.right, .alert.top, .alert.bottom {
  padding-top: $alert-padding-y / 3;
  padding-bottom: $alert-padding-y / 3;

  @include media-breakpoint-down(sm) {
    display: none !important; // overwrite inline style
  }

  &:before, &:after {
    border: solid transparent;
    content: " ";
    height: 0;
    width: 0;
    position: absolute;
    pointer-events: none;
  }
}

.alert.bottom {
  &:before, &:after {
    bottom: 100%;
    left: 5%;
  }
}

.alert.left {
  &:before, &:after {
    right: 100%;
    top: 14px;
  }
}

.alert.right {
  &:before, &:after {
    left: 100%;
    top: 14px;
  }
}

.alert.top {
  &:before, &:after {
    top: 100%;
    left: 5%;
  }
}

$arrow-size: 8px;

.alert.left:after {
  border-right-color: $warning;
  border-width: $arrow-size;
  margin-top: -$arrow-size;
}

.alert.left:before {
  border-right-color: theme-color-level('warning', $alert-border-level);
  border-width: $arrow-size + 3px;
  margin-top: -($arrow-size + 3px);
}

.alert.right:after {
  border-left-color: theme-color-level('warning', $alert-bg-level);
  border-width: $arrow-size;
  margin-top: -$arrow-size;
}

.alert.right:before {
  border-left-color: theme-color-level('warning', $alert-border-level);
  border-width: $arrow-size + 3px;
  margin-top: -($arrow-size + 3px);
}

.alert.bottom:after {
  border-bottom-color: theme-color-level('warning', $alert-bg-level);
  border-width: $arrow-size;
  margin-left: -$arrow-size;
}

.alert.bottom:before {
  border-bottom-color: theme-color-level('warning', $alert-border-level);
  border-width: $arrow-size + 3px;
  margin-left: -($arrow-size + 3px);
}

.alert.top:after {
  border-top-color: theme-color-level('warning', $alert-bg-level);
  border-width: $arrow-size;
  margin-left: -$arrow-size;
}

.alert.top:before {
  border-top-color: theme-color-level('warning', $alert-border-level);
  border-width: $arrow-size + 3px;
  margin-left: -($arrow-size + 3px);
}
</style>
