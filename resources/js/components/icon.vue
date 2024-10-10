<template>
  <i
    :class="[fontAwesomeIconClass, 'fa-fw', {'fa-spin':this.$props.spin}]"
    :data-icon="$props.name"
  />
</template>

<script lang="ts">
import {inject} from 'vue';

export default {
  name: 'VueIcon',
  props: {
    name: {type: String},
    spin: {type: Boolean},
    empty: {type: Boolean},
  },
  computed: {
    fontAwesomeIconClass(): string {
      if (this.$props.empty) {
        return 'fa';
      }
      const icons = inject('icons');
      return icons[this.$props.name] as string;
    },
  },
};

export function iconHtml(iconName: string, options: object = {}): string {
  return `<i class="${iconClass(iconName, options)}"></i>`;
}

function iconClass(iconName: string, options: object): string {
  const icons = window['icons'];
  return [
    icons[iconName],
    'fa-fw',
    options.spin ? ' fa-spin' : '',
    options.class ? options.class : '',
  ].join(' ');
}
</script>
