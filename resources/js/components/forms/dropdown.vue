<template>
  <ol v-on-clickaway="hideDropdown" ref="dropdown" class="auto-complete" v-show="isDropdownVisible">
    <li v-for="(item, index) in items" :key="index" :class="{'hover': index === selectedIndex}" @click="selectItem" @mouseover="hoverItem(index)">

      <slot name="item" :item="item">
        <vue-avatar :photo="item.photo" :name="item.name" class="d-inline-block"></vue-avatar>

        <span>{{ item.name }}</span>

        <small v-if="item.group" class="badge badge-secondary">{{ item.group }}</small>
      </slot>
    </li>
  </ol>
</template>

<script>
  import VueAvatar from '../avatar.vue';
  import { mixin as clickaway } from 'vue-clickaway';

  export default {
    components: { 'vue-avatar': VueAvatar },
    mixins: [ clickaway ],
    props: {
      items: {
        type: Array,
        default: () => []
      },
      defaultIndex: {
        type: Number,
        default: 0
      }
    },
    data() {
      return {
        isDropdownVisible: false,
        selectedIndex: this.defaultIndex
      }
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
          }
          else if (index < 0) {
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
          console.log(dropdown.scrollTop)
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
      }
    },
    watch: {
      items(newItems, oldItems) {
        this.toggleDropdown(Boolean(newItems.length));

        // reset position and set scrollbar
        this.selectedIndex = this.defaultIndex;
        this.adjustScrollbar();
      }
    }
  }
</script>
