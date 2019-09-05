<template>
    <input type="text" :name="name" v-model="valueLocal" class="form-control">
</template>

<script>
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
        computed: {
            valueLocal: {
                get: function () {
                    return this.value;
                },
                set: function (value) {
                    this.$emit('update:value', value);
                }
            }
        }
    };
</script>
