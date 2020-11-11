<template>
  <div id="box-edit-firm" class="card card-default">
    <div class="card-header">
      Dane firmy
    </div>

    <div class="card-body">
      <vue-form-group :errors="errors['name']" label="Nazwa firmy" class="border-bottom">
        <div class="input-group">
          <vue-text v-model="firm.name" :is-invalid="'name' in errors"></vue-text>

          <div class="input-group-append">
            <a class="input-group-text text-decoration-none" href="javascript:" title="Dodaj nową firmę"><i class="fas fa-plus"></i></a>
          </div>
        </div>

        <span class="form-text text-muted">Podając nazwę firmy, oferta staje się bardziej wiarygodna i wartościowa.</span>
      </vue-form-group>

      <div class="border-bottom form-group">
        <div class="form-group">
          <div class="custom-control custom-radio">
            <input type="radio" id="is_agency_0" class="is_agency custom-control-input" name="is_agency" v-model="firm.is_agency" :value="false"></input>

            <label for="is_agency_0" class="custom-control-label">Bezpośredni pracodawca</label>
          </div>
        </div>

        <div class="form-group">
          <div class="custom-control custom-radio">
            <input type="radio" id="is_agency_1" class="is_agency custom-control-input" name="is_agency" v-model="firm.is_agency" :value="true"></input>

            <label for="is_agency_1" class="custom-control-label">Agencja pośrednictwa / IT outsourcing</label>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="col-form-label">Logo</label>

        <div class="row">
          <div class="col-sm-2">
            <vue-thumbnail
              :url="firm.logo"
              :file="firm.logo"
              name="logo"
              upload-url="/Firm/Logo"
              @upload="ADD_LOGO"
              @delete="REMOVE_LOGO">
            </vue-thumbnail>
          </div>
        </div>
      </div>

      <vue-form-group :errors="errors['website']" label="Strona WWW" class="border-bottom">
        <vue-text name="website" :value.sync="firm.website" :is-invalid="'website' in errors"></vue-text>

        <span class="form-text text-muted">Firmowa strona WWW. Będzie ona wyświetlana przy ofercie.</span>
      </vue-form-group>

      <vue-form-group label="Opis firmy" class="border-bottom">
        <vue-tinymce v-model="firm.description" :init="tinyMceOptions"></vue-tinymce>

<!--        <input type="hidden" name="description" v-model="firm.description"></input>-->

        <span class="form-text text-muted">Czym zajmuje się firma, w jakich branżach działa oraz jakie technologie wykorzystuje?</span>
      </vue-form-group>

      <div class="form-group border-bottom" v-show="firm.is_agency === false">
        <label class="col-form-label">Dodaj zdjęcia</label>

        <div class="row mb-2">
          <div class="col-sm-2" v-for="photo in gallery">
            <vue-thumbnail
              :url="photo"
              :file="photo"
              upload-url="/Firm/Gallery"
              @upload="ADD_PHOTO"
              @delete="REMOVE_PHOTO">
            </vue-thumbnail>
          </div>
        </div>
      </div>

      <vue-form-group :errors="errors['youtube_url']" label="Nagranie wideo w Youtube" class="form-group">
        <vue-text name="youtube_url" v-model="firm.youtube_url" :is-invalid="'youtube_url' in errors"></vue-text>

        <span class="form-text text-muted">Film promujący firmę będzie wyświetlany pod ogłoszeniem o pracę.</span>
      </vue-form-group>

      <vue-form-group :errors="errors['employees']" label="Liczba pracowników w firmie">
        <vue-select name="employees" :options="employees" v-model="firm.employees" placeholder="--"></vue-select>

        <span class="form-text text-muted">Pozwala ocenić jak duża jest firma. Czy jest to korporacja, czy mała rodzinna firma?</span>
      </vue-form-group>

      <vue-form-group :errors="errors['founded']" label="Rok powstania" class="border-bottom">
        <vue-select name="founded" :options="founded" v-model="firm.founded" placeholder="--"></vue-select>

        <span class="form-text text-muted">Pozwala ocenić jak duża jest firma. Czy jest to korporacja, czy mała rodzinna firma?</span>
      </vue-form-group>

      <vue-form-group label="Adres" class="border-bottom" v-show="firm.is_agency === false">
        <vue-text autocomplete="off" v-model="address" @keydown.native.enter.prevent="changeAddress"></vue-text>

        <span class="form-text text-muted">Wpisz adres i naciśnij Enter lub kliknij na mapę. Adres firmy będzie wyświetlany przy ofercie.</span>

<!--        <input type="hidden" name="latitude" v-model="firm.latitude">-->
<!--        <input type="hidden" name="longitude" v-model="firm.longitude">-->
<!--        <input type="hidden" name="country_id" v-model="firm.country_id">-->
<!--        <input type="hidden" name="street" v-model="firm.street">-->
<!--        <input type="hidden" name="city" v-model="firm.city">-->
<!--        <input type="hidden" name="country" v-model="firm.country">-->
<!--        <input type="hidden" name="postcode" v-model="firm.postcode">-->
<!--        <input type="hidden" name="street_number" v-model="firm.street_number">-->
<!--        <input type="hidden" name="address" v-model="firm.address">-->

        <div id="map">
          <vue-map @click="geocode" class="h-100" :latitude="firm.latitude || 51.919438" :longitude="firm.longitude || 19.145135999">
            <vue-marker :latitude="firm.latitude" :longitude="firm.longitude"></vue-marker>
          </vue-map>
        </div>
      </vue-form-group>

      <div class="form-group border-bottom" v-show="firm.is_agency === false">
        <label class="col-form-label">Benefity</label>
        <span class="form-text text-muted">Kliknij na wybraną pozycję, aby zaznaczyć benefity jakie oferuje Twoja firma. Jeżeli nie ma go na liście, możesz dodać nową pozycję wpisując ją w polu poniżej.</span>

        <ol class="benefits list-group list-group-horizontal d-flex flex-row flex-wrap">

          <li
            class="list-group-item w-50 clickable"
            v-for="benefit in defaultBenefits"
            :class="{checked: firm.benefits.includes(benefit)}"
            @click="TOGGLE_BENEFIT(benefit)"
          >
            <i class="fas fa-fw " :class="{'fa-check': firm.benefits.includes(benefit), 'fa-times': !firm.benefits.includes(benefit)}"></i> {{ benefit }}
          </li>

          <li class="list-group-item w-50 checked" v-for="benefit in firm.benefits" v-if="!defaultBenefits.includes(benefit)">
            <i class="fas fa-fw fa-check"></i>

            <input type="text" maxlength="100" :value="benefit" class="form-control form-control-sm" @keydown.enter.prevent="">
            <button class="btn btn-sm btn-delete" title="Usuń tę pozycję" @click.prevent="REMOVE_BENEFIT(benefit)"><i class="fas fa-minus-circle text-danger"></i></button>
          </li>

          <li class="list-group-item w-50 checked">
            <i class="fas fa-fw fa-check"></i>

            <input v-model="benefit" type="text" maxlength="100" class="form-control form-control-sm" @keydown.enter.prevent="addBenefit" placeholder="Naciśnij Enter, aby dodać">
          </li>
        </ol>

        <div class="clearfix"></div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import VueFormGroup from '@/js/components/forms/form-group.vue';
  import VueText from '@/js/components/forms/text.vue';
  import VueSelect from '@/js/components/forms/select.vue';
  import VueButton from '@/js/components/forms/button.vue';
  import VueError from '@/js/components/forms/error.vue';
  import VueTinyMce from '@tinymce/tinymce-vue';
  import VueMap from '@/js/components/google-maps/map.vue';
  import VueMarker from '@/js/components/google-maps/marker.vue';
  import VueThumbnail from '@/js/components/thumbnail.vue';
  import { Prop } from "vue-property-decorator";
  import { Job, Firm } from '../../types/models';
  import { mapMutations, mapActions } from "vuex";
  import Geocoder from '../../libs/geocoder';
  import TinyMceOptions from '../../libs/tinymce';
  import store from '../../store';

  @Component({
    components: {
      'vue-form-group': VueFormGroup,
      'vue-text': VueText,
      'vue-select': VueSelect,
      'vue-button': VueButton,
      'vue-error': VueError,
      'vue-tinymce': VueTinyMce,
      'vue-map': VueMap,
      'vue-marker': VueMarker,
      'vue-thumbnail': VueThumbnail,
    },
    methods: {
      ...mapMutations('jobs', ['REMOVE_BENEFIT', 'TOGGLE_BENEFIT', 'ADD_LOGO', 'REMOVE_LOGO', 'ADD_PHOTO', 'REMOVE_PHOTO'])
    },
    // watch: {
    //   firm: {
    //     handler(firm) {
    //       // store.commit('jobs/SET_FIRM', firm);
    //     },
    //     deep: true
    //   }
    // },
  })
  export default class VueFirmForm extends Vue {
    @Prop()
    firm!: Firm;

    @Prop()
    errors;

    @Prop()
    defaultBenefits!: string[];

    @Prop()
    employees!: number[];

    benefit: string = '';

    changeAddress(e) {
      const val = e.target.value.trim();
      const geocoder = new Geocoder();

      if (val.length) {
        geocoder.geocode(val, result => {
          this.firm = Object.assign(this.firm, result);
        });
      } else {
        ['longitude', 'latitude', 'country', 'city', 'street', 'street_number', 'postcode'].forEach(field => {
          this.firm[field] = null;
        });
      }
    }

    geocode(latlng) {
      const geocoder = new Geocoder();

      geocoder.reverseGeocode(latlng, result => this.firm = Object.assign(this.firm, result));
    }

    addBenefit() {
      if (this.benefit.trim()) {
        store.commit('jobs/ADD_BENEFIT', this.benefit);
      }

      this.benefit = '';
    }

    get address() {
      return String((this.firm.street || '') + ' ' + (this.firm.street_number || '') + ' ' + (this.firm.postcode || '') + ' ' + (this.firm.city || '')).trim();
    }

    get gallery() {
      return this.firm.gallery && this.firm.gallery.length ? this.firm.gallery : {'file': ''};
    }

    get founded() {
      const year = new Date().getFullYear();
      let result = {};

      for (let i = 1900; i <= year; i++) {
        result[i] = `${i}%`;
      }

      return result;
    }

    get tinyMceOptions() {
      return TinyMceOptions;
    }
  }
</script>
