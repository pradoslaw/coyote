<template>
  <input ref="autocomplete" :value="label" autocomplete="off" class="form-control" placeholder="Np. Warszawa, al. Jerozolimskie 3" type="text" @keydown.enter.prevent="" @blur="search"/>
</template>

<script>
  export default {
    props: {
      label: {
        type: String
      }
    },
    mounted() {
      let data = {};
      const autocomplete = new google.maps.places.Autocomplete(this.$refs.autocomplete, {types: ['geocode']});

      autocomplete.addListener('place_changed', () => {
        let place = autocomplete.getPlace();

        if (!place.geometry) {
          return;
        }

        data = Object.assign({latitude: place.geometry.location.lat(), longitude: place.geometry.location.lng()}, this.map(place.address_components));

        this.$emit('change', data);
      });
    },
    methods: {
      map(components) {
        let result = {};

        components.forEach(item => {
          switch (item.types[0]) {
            case 'street_number':
              result.street_number = item.long_name;
              break;
            case 'route':
              result.street = item.long_name;
              break;
            case 'neighborhood':
            case 'locality':
              result.city = item.long_name;
              break;
            case 'postal_code':
              result.postcode = item.long_name;
              break;
            case 'country':
              result.country = item.long_name;
              break;
          }
        });

        return result;
      },

      search() {
        // add setTimeout because onblur event occurs first, before place_changed.
        setTimeout(() => {
          if (Object.keys(data).length !== 0) {
            return;
          }

          const geocoder = new google.maps.Geocoder();

          geocoder.geocode({'address': this.$refs.autocomplete.value}, (result, status) => {
            if (status !== google.maps.GeocoderStatus.OK) {
              return;
            }

            let components = result[0].address_components;
            let location = result[0].geometry.location;

            data = Object.assign({longitude: location.lng(), latitude: location.lat()}, this.map(components));

            this.$emit('change', data);
          });
        }, 100);
      }
    }
  }
</script>
