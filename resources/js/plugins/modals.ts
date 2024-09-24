import Vue from 'vue';
import VueModal from "../components/modal.vue";

interface ModalOptions {
  message: string;
  title: string;
  okLabel: string;
}

export function confirmModal(options: ModalOptions): Promise<void> {
  const modalRef = Math.random().toString(36).substring(5);
  let domElement;

  const ModalWrapper = {
    data: () => ({...options, modalRef}),
    props: {
      resolver: Function,
    },
    components: {'vue-modal': VueModal},
    mounted() {
      this.$refs[modalRef].open();
    },
    methods: {
      resolve(): void {
        this.resolver();
        this.destroy();
      },
      destroy(): void {
        this.$refs[modalRef].close();
        this.$destroy();
        domElement.parentNode!.removeChild(domElement);
      },
    },
    template: `
      <vue-modal :ref="modalRef">
        <template v-slot:title>{{ title }}</template>
        <div v-html="message"></div>
        <template v-slot:buttons>
          <button @click="destroy" type="button" class="btn btn-secondary">Anuluj</button>
          <button @click="resolve" type="submit" class="btn btn-danger danger">{{ okLabel || 'Ok' }}</button>
        </template>
      </vue-modal>
    `,
  };

  return new Promise(resolve => {
    const wrapper = new (Vue.extend(ModalWrapper))({propsData: {resolver: resolve}}).$mount();
    domElement = wrapper.$el;
    document.body.append(domElement);
  });
}
