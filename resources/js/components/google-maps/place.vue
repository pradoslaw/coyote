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
    data() {
      return {
        isFound: false
      }
    },
    mounted() {
      const autocomplete = new google.maps.places.Autocomplete(this.$refs.autocomplete, {types: ['geocode']});

      autocomplete.addListener('place_changed', () => {
        let place = autocomplete.getPlace();

        this.isFound = false;

        if (!place.geometry) {
          return;
        }

        this.isFound = true;
        this.$emit('change', this.map(place.geometry.location.lat(), place.geometry.location.lng(), place.address_components));
      });
    },
    methods: {
      map(latitude, longitude, components) {
        let result = { latitude, longitude };

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

        result.label = [(`${result.street ?? ''} ${result.street_number ?? ''}`).trim(), result.city, result.country].filter(item => item !== '').join(', ');

        return result;
      },

      search() {
        // add setTimeout because onblur event occurs first, before place_changed.
        setTimeout(() => {
          if (this.isFound) {
            return;
          }

          const geocoder = new google.maps.Geocoder();

          geocoder.geocode({'address': this.$refs.autocomplete.value}, (result, status) => {
            if (status !== google.maps.GeocoderStatus.OK) {
              return;
            }

            let components = result[0].address_components;
            let location = result[0].geometry.location;

            this.$emit('change', this.map(location.lat(), location.lng(), components));
          });
        }, 100);
      }
    }
  }
</script>
