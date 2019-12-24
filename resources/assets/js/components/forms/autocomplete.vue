<template>
  <div style="position: relative">
    <input
      type="text"
      class="form-control"
      :id="id"
      :placeholder="placeholder"
      autocomplete="off"
      v-model="valueLocal"
      @click="emitFocus"
      @focus="emitFocus"
      @keyup.up.prevent="goUp"
      @keyup.down.prevent="goDown"
      @keyup.esc="hideDropdown"
      @keydown.enter.prevent="selectItem"
    >

    <ol ref="dropdown" class="auto-complete" style="width: 100%" v-show="isDropdownShown">
      <li v-for="(item, index) in items" :key="index" :class="{'hover': index === selectedIndex}" @click="selectItem" @mouseover="hoverItem(index)">

        <slot name="item" :item="item">
          <object :data="item.photo || '//'" type="image/png">
            <img src="/img/avatar.png" style="width: 100%">
          </object>

          <span>{{ item.name }}</span>

          <small v-if="item.group" class="label label-default">{{ item.group }}</small>
        </slot>
      </li>
    </ol>
  </div>
</template>

<script>
  import { default as mixins } from '../mixins/form';

  export default {
    mixins: [ mixins ],
    props: {
      items: {
        type: Array,
        default: () => []
      },
      placeholder: {
        type: String
      },
      id: {
        type: String
      },
      value: {
        type: String,
        default: ''
      }
    },
    data() {
      return {
        isDropdownShown: false,
        selectedIndex: -1
      }
    },
    mounted() {
      document.body.addEventListener('click', event => {
        if (!(this.$el === event.target || this.$el.contains(event.target))) {
          this.isDropdownShown = false;
        }
      });
    },
    methods: {
      emitFocus() {
        this.$emit('focus');
      },

      goDown() {
        this.isDropdownShown = true;

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
        if (this.selectedIndex > -1) {
          this.$emit('select', this.items[this.selectedIndex]);
        }

        this.hideDropdown();
      },

      // inputChange(e) {
      //   if (e.key !== 'Enter') {
      //     this.$emit('change', this.vModel);
      //   }
      // },

      hoverItem(index) {
        this.selectedIndex = index;
      },

      toggleDropdown(flag) {
        this.isDropdownShown = flag;
      },

      hideDropdown() {
        this.isDropdownShown = false;
        this.selectedIndex = -1;
      }
    }
  }
</script>
