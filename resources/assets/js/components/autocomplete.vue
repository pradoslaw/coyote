<template>
  <div style="position: relative">
    <input
      type="text"
      class="form-control"
      :id="id"
      :placeholder="placeholder"
      autocomplete="off"
      v-model="vModel"
      @click="emitFocus"
      @focus="emitFocus"
      @keyup.up.prevent="goUp"
      @keyup.down.prevent="goDown"
      @keyup.esc="hideDropdown"
      @keyup="inputChange"
      @keydown.enter.prevent="selectItem"
    >

    <ol ref="dropdown" class="auto-complete" style="width: 100%" v-show="isDropdownShown">
      <li v-for="(item, index) in items" :key="index" :class="{'hover': index === hoverIndex}" @click="selectItem" @mouseover="hoverItem(index)">

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
  export default {
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
      }
    },
    data() {
      return {
        isDropdownShown: false,
        hoverIndex: -1,
        vModel: ''
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

        if (this.hoverIndex < this.items.length - 1) {
          this.hoverIndex += 1;
        }

        this.adjustScrollbar();
      },

      goUp() {
        if (this.hoverIndex > 0) {
          this.hoverIndex -= 1;
        }

        this.adjustScrollbar();
      },

      adjustScrollbar() {
        let dropdown = this.$refs['dropdown'];

        if (dropdown.children.length) {
          dropdown.scrollTop = this.hoverItem * dropdown.children[0].offsetHeight;
        }
      },

      selectItem() {
        if (this.hoverIndex > -1) {
          this.$emit('select', this.items[this.hoverIndex]);
        }

        this.hideDropdown();
      },

      inputChange(e) {
        if (e.key !== 'Enter') {
          this.$emit('change', this.vModel);
        }
      },

      hoverItem(index) {
        this.hoverIndex = index;
      },

      toggleDropdown(flag) {
        this.isDropdownShown = flag;
      },

      hideDropdown() {
        this.isDropdownShown = false;
        this.vModel = '';
        this.hoverIndex = -1;
      }
    }
  }
</script>
