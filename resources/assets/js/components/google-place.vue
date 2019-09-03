<template>
    <input ref="autocomplete" class="form-control" placeholder="Np. Warszawa, al. Jerozolimskie 3" type="text"/>
</template>

<script>
    export default {
        mounted() {
            const autocomplete = new google.maps.places.Autocomplete(this.$refs.autocomplete, {types: ['geocode']});

            autocomplete.addListener('place_changed', () => {
                let place = autocomplete.getPlace();

                this.$emit('change', {latitude: place.geometry.location.lat, longitude: place.geometry.location.lng, label: place.formatted_address});
            });
        }
    }
</script>
