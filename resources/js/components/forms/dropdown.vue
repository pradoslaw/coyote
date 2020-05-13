<template>
  <ol ref="dropdown" class="auto-complete w-100" v-show="isDropdownVisible">
    <li v-for="(item, index) in items" :key="index" :class="{'hover': index === selectedIndex}" @click="selectItem" @mouseover="hoverItem(index)">

      <slot name="item" :item="item">
        <object :data="item.photo || '//'" type="image/png">
          <img src="/img/avatar.png" class="w-100">
        </object>

        <span>{{ item.name }}</span>

        <small v-if="item.group" class="badge badge-secondary">{{ item.group }}</small>
      </slot>
    </li>
  </ol>
</template>

<script>
  export default {
    props: {
      items: {
        type: Array,
        default: () => []
      }
    },
    data() {
      return {
        isDropdownVisible: false,
        selectedIndex: -1
      }
    },
    mounted() {
      document.body.addEventListener('click', event => {
        if (!(this.$el === event.target || this.$el.contains(event.target))) {
          this.isDropdownVisible = false;
        }
      });
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
          dropdown.scrollTop = this.hoverItem * dropdown.children[0].offsetHeight;
        }
      },

      selectItem() {
        const selected = this.getSelected();

        if (selected) {
          this.$emit('select', selected);
        }

        this.toggleDropdown(false);
      },

      hoverItem(index) {
        this.selectedIndex = index;
      },

      toggleDropdown(flag) {
        this.isDropdownVisible = flag;

        if (!flag) {
          this.selectedIndex = -1;
        }
      },

      /** @deprecated */
      hideDropdown() {
        this.isDropdownVisible = false;
        this.selectedIndex = -1;
      },

      getSelected() {
        return this.selectedIndex > -1 ? this.items[this.selectedIndex] : null;
      }
    },
    watch: {
      items(items) {
        this.toggleDropdown(items.length);
      }
    }
  }
</script>
