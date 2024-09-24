<template>
  <vue-modal ref="modal">
    <template v-slot:title>
      Zgłoś materiał
    </template>
    <slot>
      <div v-for="(type, index) in types" :key="index" class="media">
        <div class="me-2">
          <vue-radio name="type_id" v-model="selectedType" :checked-value="type.id" :id="`type-${type.id}`"></vue-radio>
        </div>
        <div class="media-body">
          <i :class="icon(type.name)" class="me-1"/>
          <label :for="`type-${type.id}`" class="fw-bold">{{ type.name }}</label>
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
import axios from 'axios';
import {notify} from "../../toast";
import VueRadio from '../forms/radio.vue';
import VueModal from '../modal.vue';

export default {
  name: 'FlagModal',
  components: {
    'vue-modal': VueModal,
    'vue-radio': VueRadio,
  },
  props: {
    url: {
      type: String,
      required: true,
    },
    metadata: {
      type: String,
      required: true,
    },
    types: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      selectedType: null as number | null,
      text: null as string | null,
      isProcessing: false,
    };
  },
  mounted() {
    this.$refs.modal.open();
  },
  beforeDestroy() {
    this.$refs.modal.close();
  },
  methods: {
    closeModal() {
      this.$emit('close');
    },
    sendReport() {
      this.isProcessing = true;

      axios.post('/Flag', {
        type_id: this.selectedType,
        url: this.url,
        metadata: this.metadata,
        text: this.text,
      })
        .then(() => {
          notify({type: 'success', text: 'Dziękujemy za wysłanie raportu.'});
          this.closeModal();
        })
        .finally(() => this.isProcessing = false);
    },
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
    },
  },
};
</script>
