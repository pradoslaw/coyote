<template>
  <vue-modal ref="modal">
    <template v-slot:title>
      Zgłoś materiał
    </template>
    <slot>
      <div v-for="(type, index) in types" :key="index" class="media">
        <div class="mr-2">
          <vue-radio name="type_id" v-model="selectedType" :checked-value="type.id" :id="`type-${type.id}`"></vue-radio>
        </div>
        <div class="media-body">
          <i :class="icon(type.name)" class="mr-1"/>
          <label :for="`type-${type.id}`" class="font-weight-bold">{{ type.name }}</label>
          <p>{{ type.description }}</p>
        </div>
      </div>
      <textarea
        v-model="text"
        placeholder="Dodatkowe informacje"
        name="text"
        class="form-control"
      ></textarea>
    </slot>
    <template v-slot:buttons>
      <button @click="closeModal" type="button" class="btn btn-secondary">Anuluj</button>
      <button @click="sendReport" :disabled="isProcessing || selectedType === null" type="submit" class="btn btn-danger danger">
        Wyślij raport
      </button>
    </template>
  </vue-modal>
</template>

<script lang="ts">
import {FlagType} from "@/types/models";
import axios from 'axios';
import Vue from 'vue';
import Component from "vue-class-component";
import {Prop, Ref} from "vue-property-decorator";

import VueRadio from '../forms/radio.vue';
import VueModal from '../modal.vue';

@Component({
  components: {'vue-modal': VueModal, 'vue-radio': VueRadio},
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

    axios.post('/Flag', {type_id: this.selectedType, url: this.url, metadata: this.metadata, text: this.text})
      .then(() => {
        this.$notify({type: 'success', text: 'Dziękujemy za wysłanie raportu.'});
        this.closeModal();
      })
      .finally(() => this.isProcessing = false);
  }

  icon(name: string): string {
    const icons = {
      'Spam': 'fas fa-envelopes-bulk',
      'Wulgaryzmy': 'fas fa-book-skull',
      'Off-Topic': 'fas fa-wave-square',
      'Nieprawidłowa kategoria': 'fas fa-table-list',
      'Próba wyłudzenia gotowca': 'fas fa-user-graduate',
      'Inne': "far fa-flag",
    };
    return icons[name];
  }
}
</script>
