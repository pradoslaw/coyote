<template>
  <form>
    <vue-form-group :errors="errors['email']" label="Email" help="Nie wysyłamy spamu! Obiecujemy.">
      <vue-text name="email" v-model="applicationSync.email" :is-invalid="'email' in errors"></vue-text>
    </vue-form-group>

    <vue-form-group :errors="errors['name']" label="Imię i nazwisko">
      <vue-text name="name" v-model="applicationSync.name" :is-invalid="'name' in errors"></vue-text>
    </vue-form-group>

    <vue-form-group :errors="errors['phone']" label="Numer telefonu" help="Podanie numeru telefonu nie jest obowiązkowe, ale pozwoli na szybki kontakt.">
      <vue-text name="phone" v-model="applicationSync.phone" :is-invalid="'phone' in errors"></vue-text>
    </vue-form-group>

    <div class="form-group">
      <label class="col-form-label">CV/Resume</label>

      <vue-thumbnail :url="applicationSync.cv" upload-url="/Praca/Upload" name="cv" @upload="addAsset" @delete="deleteAsset" class="w-25"></vue-thumbnail>

      <span class="form-text">CV/résumé z rozszerzeniem *.pdf, *.doc, *.docx lub *.rtf. Maksymalnie 5 MB.</span>
    </div>

    <vue-form-group :errors="errors['github']" label="Konto Github" class="github">
      <vue-text name="github" v-model="applicationSync.github" :is-invalid="'github' in errors"></vue-text>
    </vue-form-group>

    <vue-form-group :errors="errors['salary']" label="Minimalne oczekiwania wynagrodzenie">
      <vue-select v-model="applicationSync.salary" :options="salaryChoices" placeholder="Do negocjacji"></vue-select>
    </vue-form-group>

    <vue-form-group :errors="errors['dismissal_period']" label="Obecny okres wypowiedzenia">
      <vue-select v-model="applicationSync.dismissal_period" :options="dismissalPeriod" placeholder="Nie określono"></vue-select>
    </vue-form-group>

    <vue-form-group :errors="errors['text']" label="Wiadomość dla pracodawcy/zleceniodawcy" help="Taką wiadomość otrzyma osoba, która wystawiła ogłoszenie">
      <vue-tinymce v-model="applicationSync.text" :init="tinyMceOptions"></vue-tinymce>

      <input type="hidden" name="text" v-model="applicationSync.text">
    </vue-form-group>

    <div class="form-group">
      <div class="custom-control custom-checkbox">
        <vue-checkbox v-model="applicationSync.remember" id="enable-invoice" class="custom-control-input"></vue-checkbox>

        <label class="custom-control-label" for="enable-invoice">
          Zapamiętaj dane podane w formularzu
        </label>
      </div>
    </div>

    <div class="form-group">
      <vue-button :disabled="isProcessing" @click.native.prevent="submitForm" class="btn btn-primary">Wyślij</vue-button>
    </div>
  </form>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import VueFormGroup from '@/components/forms/form-group.vue';
  import VueText from '@/components/forms/text.vue';
  import VueSelect from '@/components/forms/select.vue';
  import VueCheckbox from '@/components/forms/checkbox.vue';
  import VueButton from '@/components/forms/button.vue';
  import VueError from '@/components/forms/error.vue';
  import VueThumbnail from "@/components/thumbnail.vue";
  import VueTinyMce from '@tinymce/tinymce-vue';
  import { Prop, PropSync } from "vue-property-decorator";
  import { Application, Job, Asset } from '@/types/models';
  import TinyMceOptions from '@/libs/tinymce';
  import axios from 'axios';

  @Component({
    components: {
      'vue-form-group': VueFormGroup,
      'vue-text': VueText,
      'vue-select': VueSelect,
      'vue-checkbox': VueCheckbox,
      'vue-button': VueButton,
      'vue-error': VueError,
      'vue-tinymce': VueTinyMce,
      'vue-thumbnail': VueThumbnail
    }
  })
  export default class VueApplicationForm extends Vue {
    @PropSync('application')
    applicationSync!: Application;

    @Prop(Object)
    job!: Job;

    private errors = {};
    private isProcessing = false;

    submitForm() {
      this.isProcessing = true;

      axios.post<any>(`/Praca/Application/${this.job.id}`, this.applicationSync)
        .then(result => window.location.href = result.data as string)
        .catch(err => {
          if (err.response.status !== 422) {
            return;
          }

          this.errors = err.response.data.errors;
        })
        .finally(() => this.isProcessing = false);
    }

    addAsset(asset: Asset) {
      Vue.set(this.applicationSync, 'cv', asset.filename!);
    }

    deleteAsset() {
      this.applicationSync.cv = null;
    }

    get tinyMceOptions() {
      return TinyMceOptions;
    }

    get dismissalPeriod() {
      return [
        'Brak',
        '3 dni robocze',
        '1 tydzień',
        '2 tygodnie',
        '1 miesiąc',
        '3 miesiące'
      ].reduce((acc, value) => {
        acc[value] = value;

        return acc;
      }, {})
    }

    get salaryChoices() {
      return [
        'od 1000 zł m-c',
        'od 2000 zł m-c',
        'od 3000 zł m-c',
        'od 4000 zł m-c',
        'od 5000 zł m-c',
        'od 6000 zł m-c',
        'od 7000 zł m-c',
        'od 8000 zł m-c',
        'od 9000 zł m-c',
        'od 10000 zł m-c',
      ].reduce((acc, value) => {
        acc[value] = value;

        return acc;
      }, {})
    }
  }
</script>
