<template></template>

<script>
    export default {
        inject: ['getMap'],
        props: {
            latitude: [Number, String],
            longitude: [Number, String]
        },
        mounted() {
            this.getMap(map => {
                this.map = map;

                this.addMarker();

                this.$watch('latitude', this.onUpdate);
                this.$watch('longitude', this.onUpdate);
            });
        },
        methods: {
            addMarker() {
                if (!this.latitude || !this.longitude) {
                    return;
                }

                this.marker = new google.maps.Marker({map: this.map, position: this.getPosition()});
            },

            onUpdate() {
                if (!this.marker) {
                    this.addMarker();

                    return;
                }

                if (!this.latitude || !this.longitude) {
                    this.marker.setMap(null);
                    this.marker = null;

                    return;
                }

                let position = this.getPosition();

                this.marker.setPosition(position);
                this.map.setCenter(position);
            },

            getPosition() {
                return new google.maps.LatLng(this.latitude, this.longitude);
            }
        }
    };
</script>
