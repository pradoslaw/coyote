<template>
    <input ref="autocomplete" autocomplete="off" class="form-control" placeholder="Np. Warszawa, al. Jerozolimskie 3" type="text"/>
</template>

<script>
    export default {
        mounted() {
            const autocomplete = new google.maps.places.Autocomplete(this.$refs.autocomplete, {types: ['geocode']});

            autocomplete.addListener('place_changed', () => {
                let place = autocomplete.getPlace();

                let data = {latitude: place.geometry.location.lat(), longitude: place.geometry.location.lng()};

                place.address_components.forEach(item => {
                    switch (item.types[0]) {
                        case 'data':
                            address.street_number = item.long_name;
                            break;
                        case 'route':
                            data.street = item.long_name;
                            break;
                        case 'neighborhood':
                        case 'locality':
                            data.city = item.long_name;
                            break;
                        case 'postal_code':
                            data.post_code = item.long_name;
                            break;
                        case 'country':
                            data.country = item.long_name;
                            break;
                    }
                });

                data.address = `${data.street} ${data.street_number}`.trim();

                this.$emit('change', data);
            });
        }
    }
</script>
