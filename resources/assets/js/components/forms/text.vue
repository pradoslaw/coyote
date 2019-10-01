<template>
    <input type="text" :name="name" v-model="valueLocal" class="form-control">
</template>

<script>
    import { default as mixins } from '../mixins/form';

    export default {
        props: {
            name: {
                type: String,
                require: true
            },
            value: {
                type: [String, Number]
            }
        },
        mixins: [ mixins ]
    };
</script>
