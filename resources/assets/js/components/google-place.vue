<template>
    <input ref="autocomplete" :value="label" autocomplete="off" class="form-control" placeholder="Np. Warszawa, al. Jerozolimskie 3" type="text" @keydown.enter.prevent=""/>
</template>

<script>
    export default {
        props: {
            label: {
                type: String
            }
        },
        mounted() {
            const autocomplete = new google.maps.places.Autocomplete(this.$refs.autocomplete, {types: ['geocode']});

            autocomplete.addListener('place_changed', () => {
                let place = autocomplete.getPlace();

                if (!place.geometry) {
                    return;
                }

                let data = {latitude: place.geometry.location.lat(), longitude: place.geometry.location.lng()};

                place.address_components.forEach(item => {
                    switch (item.types[0]) {
                        case 'street_number':
                            data.street_number = item.long_name;
                            break;
                        case 'route':
                            data.street = item.long_name;
                            break;
                        case 'neighborhood':
                        case 'locality':
                            data.city = item.long_name;
                            break;
                        case 'postal_code':
                            data.postcode = item.long_name;
                            break;
                        case 'country':
                            data.country = item.long_name;
                            break;
                    }
                });

                this.$emit('change', data);
            });
        }
    }
</script>
