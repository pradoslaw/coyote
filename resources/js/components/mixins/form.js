export default {
    computed: {
        valueLocal: {
            get: function () {
                return this.value;
            },
            set: function (value) {
                this.$emit('update:value', value);
                this.$emit('input', value);
            }
        }
    }
};
