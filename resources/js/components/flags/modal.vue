<template>
  <vue-modal ref="modal">
    <template v-slot:title>
      Chcę zgłosić ten materiał w związku z...
    </template>

    <slot>
      <div v-for="(type, index) in types" class="media">
        <div class="mr-2">
          <vue-radio name="type_id" :value.sync="selectedType" :checked-value="type.id" :id="`type-${type.id}`"></vue-radio>
        </div>

        <div class="media-body">
          <label :for="`type-${type.id}`" class="font-weight-bold">{{ type.name }}</label>

          <p>{{ type.description }}</p>

          <textarea
            v-if="index + 1 === types.length && selectedType === type.id"
            v-model="text"
            name="text"
            class="form-control"
          ></textarea>
        </div>
      </div>
    </slot>

    <template v-slot:buttons>
      <button @click="closeModal" type="button" class="btn btn-secondary">Anuluj</button>
      <button @click="sendReport" :disabled="isProcessing || selectedType === null" type="submit" class="btn btn-danger danger">Wyślij raport</button>
    </template>
  </vue-modal>
</template>

<script lang="ts">

import Vue from 'vue';
import Component from "vue-class-component";
import { Prop, Ref } from "vue-property-decorator";
import VueModal from '../modal.vue';
import VueRadio from '../forms/radio.vue';
import { FlagType } from "../../types/models";
import axios from 'axios';

@Component({
  components: { 'vue-modal': VueModal, 'vue-radio': VueRadio }
})
export default class FlagModal extends Vue {
  @Ref('modal')
  readonly modal!: VueModal;

  @Prop()
  readonly url!: string;

  @Prop()
  protected metadata!: any;

  @Prop()
  readonly types!: FlagType[];

  selectedType: number | null = null;
  text: string | null = null;
  isProcessing = false;

  mounted() {
    this.modal.open();
  }

  beforeDestroy() {
    this.modal.close();
  }

  closeModal() {
    // destroy the vue listeners, etc
    this.$destroy();

    // remove the element from the DOM
    this.$el.parentNode!.removeChild(this.$el);
  }

  sendReport() {
    this.isProcessing = true;

    axios.post('/Flag', { type_id: this.selectedType, url: this.url, metadata: this.metadata, text: this.text })
      .then(() => {
        this.$notify({type: 'success', text: 'Dziękujemy za wysłanie raportu.'});
        this.closeModal();
      })
      .finally(() => this.isProcessing = false);
  }
}

</script>
