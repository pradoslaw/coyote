<template>
    <input :name="name" type="radio" v-model="valueLocal" :value="checkedValue">
</template>

<script>
    export default {
        props: {
            name: {
                type: String,
                require: true
            },
            value: {
                type: [String, Number, Boolean]
            },
            checkedValue: {
                type: [String, Number],
                require: false
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
