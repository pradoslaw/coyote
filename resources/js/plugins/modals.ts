import VueModal from "@/components/modal.vue";
import Vue from 'vue';

function confirmModal(options: ModalOptions) {
  const modalRef = Math.random().toString(36).substring(5);

  const ModalWrapper = Vue.extend({
    data: () => ({...options, ...{ modalRef }}),
    props: {
      resolver: Function
    },
    components: {'vue-modal': VueModal},
    mounted() {
      (this.$refs[modalRef] as VueModal).open();
    },
    methods: {
      resolve(): void {
        this.resolver();
        this.destroy();
      },

      destroy(): void {
        (this.$refs[modalRef] as VueModal).close();
        this.$destroy();

        // remove the element from the DOM
        this.$el.parentNode!.removeChild(this.$el);
      }
    },
    template: `
        <vue-modal :ref="modalRef">
          <template slot="title">{{ title }}</template>

          <div v-html="message"></div>

          <template slot="buttons">
            <button @click="destroy" type="button" class="btn btn-secondary">Anuluj</button>
            <button @click="resolve" type="submit" class="btn btn-danger danger">{{ okLabel || 'Ok' }}</button>
          </template>
        </vue-modal>
      `
  });

  return new Promise(resolve => {
    const wrapper = new ModalWrapper({propsData: {resolver: resolve}}).$mount();

    document.body.append(wrapper.$el);
  });
}

export default {
  install(Vue) {
    Vue.prototype.$confirm = confirmModal;
  }
};
