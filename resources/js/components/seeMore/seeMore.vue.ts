import VueIcon from "../icon";

export default {
  name: 'vue-see-more',
  components: {VueIcon},
  emits: ['unwrap'],
  props: {
    height: {type: Number, default: 300},
    drop: {type: Number, default: 150},
  },
  template: `
    <div class="see-more" :class="{'see-more--wrapped': wrapped}">
      <div class="see-more__content" ref="content" :style="contentCssStyle">
        <slot/>
      </div>
      <div class="see-more__unwrap" v-if="wrapped">
        <span @click="unwrap" class="neon-color-link">
          <vue-icon name="microblogFoldedUnfold"/>
          Zobacz całość
        </span>
      </div>
    </div>
  `,
  data() {
    return {
      wrapped: false,
    };
  },
  mounted(): void {
    this.$data.wrapped = this.contentHeight >= this.$props.height + this.$props.drop;
  },
  methods: {
    unwrap(): void {
      this.$data.wrapped = false;
    },
  },
  computed: {
    contentCssStyle(): object {
      if (this.$data.wrapped) {
        return {maxHeight: this.$props.height + 'px'};
      }
      return {maxHeight: 'none'};
    },
    contentHeight(): number {
      return this.$refs['content'].clientHeight;
    },
  },
};
