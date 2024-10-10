export default {
  name: 'VueIcon',
  props: {
    name: {type: String},
    spin: {type: Boolean},
    empty: {type: Boolean},
  },
  inject: ['icons'],
  template: `<i
    :class="[fontAwesomeIconClass, 'fa-fw', {'fa-spin':this.$props.spin}]"
    :data-icon="$props.name"
  />`,
  computed: {
    fontAwesomeIconClass(): string {
      if (this.$props.empty) {
        return 'fa';
      }
      const icons: object = this.icons;
      return icons[this.$props.name] as string;
    },
  },
};

export function iconHtml(icons: object, iconName: string, options: IconOptions = {}): string {
  return `<i class="${iconClass(icons, iconName, options)}"></i>`;
}

function iconClass(icons: object, iconName: string, options: IconOptions): string {
  return [
    icons[iconName],
    'fa-fw',
    options.spin ? ' fa-spin' : '',
    options.class ? options.class : '',
  ].join(' ').trim();
}

interface IconOptions {
  spin?: boolean;
  class?: string;
}
