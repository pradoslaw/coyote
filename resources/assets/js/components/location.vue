<template>
    <div v-if="remote.enabled || locations.length > 0" class="location">
        <i class="fa fa-map-marker"></i>

        <ul>
            <li v-for="location in locations"><a :href="location.url" :title="'Znajdź oferty z miasta ' + location.city">{{ location.city }}</a></li>
        </ul>

        <a v-if="remote.enabled" :href="remote.url">({{ remote.range ? `${remote.range}% pracy zdalnej` : 'praca zdalna' }})</a>
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
            }
        }
    }
</script>
