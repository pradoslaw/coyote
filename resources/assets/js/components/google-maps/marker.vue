<template>

</template>

<script>
    export default {
        inject: ['getMap'],
        props: {
            lat: String,
            lng: String
        },
        mounted() {
            this.getMap(map => {
                this.map = map;

                this.addMarker();
            });
        },
        methods: {
            addMarker() {
                new google.maps.Marker({map: this.map, position: new google.maps.LatLng(this.lat, this.lng)});
            }
        }
    };
</script>
