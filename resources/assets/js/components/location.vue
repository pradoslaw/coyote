<template>
    <div v-if="remote.enabled || locations.length > 0" class="location">
        <i class="fa fa-map-marker"></i>

        <ul>
            <li v-for="location in locations">
                <a v-if="clickable" :href="location.url" :title="'Znajdź oferty z miasta ' + location.city">{{ location.city }}</a>
                <template v-else>{{ location.city }}</template>
            </li>
        </ul>

        <a v-if="remote.enabled && clickable" :href="remote.url">({{ remoteLabel }})</a>
        <template v-else-if="!clickable">({{ remoteLabel }})</template>
    </div>
</template>

<script>
    export default {
        props: {
            remote: {
                type: Object
            },
            locations: {
                type: Array
            },
            clickable: {
                type: Boolean,
                default: true
            }
        },
        computed: {
            remoteLabel() {
                return this.remote.range ? `${this.remote.range}% pracy zdalnej` : 'praca zdalna';
            }
        }
    }
</script>
