<template>
    <input ref="autocomplete"
           placeholder="Enter your address"
           type="text"
    />
</template>

<script>
    export default {
        mounted() {
            var autocomplete = new google.maps.places.Autocomplete(
                /** @type {!HTMLInputElement} */(this.$refs.autocomplete),
                {types: ['geocode']});
        }
    }
</script>
