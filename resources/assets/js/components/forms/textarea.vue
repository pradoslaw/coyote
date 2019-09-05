<template>
    <textarea :name="name" class="form-control">{{ valueLocal }}</textarea>
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
