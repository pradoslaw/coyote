<template>
  <ol
    v-click-away="hideDropdown"
    ref="dropdown"
    class="auto-complete"
    v-show="isDropdownVisible"
    :style="containerStyle"
  >
    <li v-for="(item, index) in items"
        :key="index"
        class="d-flex align-items-center"
        :class="{'hover': index === selectedIndex}"
        @click="selectItem"
        @mouseover="hoverItem(index)"
    >
      <slot name="item" :item="item">
        <vue-avatar :photo="item.photo" :name="item.name"/>
        <span>{{ item.name }}</span>
        <small v-if="item.group" class="badge badge-secondary ms-auto">
          {{ item.group }}
        </small>
      </slot>
    </li>
  </ol>
</template>

<script>
import clickAway from '../../clickAway.js';
import VueAvatar from '../avatar.vue';

export default {
  components: {'vue-avatar': VueAvatar},
  directives: {clickAway},
  props: {
    items: {
      type: Array,
      default: () => [],
    },
    defaultIndex: {
      type: Number,
      default: 0,
    },
    rect: {
      type: Object,
    },
  },
  data() {
    return {
      isDropdownVisible: false,
      selectedIndex: this.defaultIndex,
    };
  },
  computed: {
    containerStyle() {
      if (this.$props.rect) {
        return {
          top: this.$props.rect.top + 'px',
          left: this.$props.rect.left + 'px',
        };
      }
      return {};
    },
  },
  methods: {
    goDown() {
      this.isDropdownVisible = true;
      this.changeIndex(++this.selectedIndex);
    },

    goUp() {
      this.changeIndex(--this.selectedIndex);
    },

    changeIndex(index) {
      const length = this.items.length;

      if (length > 0) {
        if (index >= length) {
          index = 0;
        } else if (index < 0) {
          index = length - 1;
        }

        this.selectedIndex = index;
        this.adjustScrollbar();
      }
    },

    adjustScrollbar() {
      let dropdown = this.$refs['dropdown'];

      if (dropdown.children.length) {
        dropdown.scrollTop = this.selectedIndex * dropdown.children[0].offsetHeight;
      }
    },

    selectItem() {
      const selected = this.getSelected();
      if (selected) {
        this.$emit('select', selected);
      }
      this.hideDropdown();
    },

    hoverItem(index) {
      this.selectedIndex = index;
    },

    toggleDropdown(flag) {
      this.isDropdownVisible = flag;
    },

    hideDropdown() {
      this.toggleDropdown(false);
      this.selectedIndex = -1;
    },

    getSelected() {
      return this.selectedIndex > -1 ? this.items[this.selectedIndex] : null;
    },
  },
  watch: {
    items(newItems, oldItems) {
      this.toggleDropdown(Boolean(newItems.length));

      // reset position and set scrollbar
      this.selectedIndex = this.defaultIndex;
      this.adjustScrollbar();
    },
  },
};
</script>
