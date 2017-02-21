<template>
    <div class="duration-changer">
        <i class="fa fa-minus-circle" @click="prev()" :class="{'disabled': !isPrev}"></i>

        <strong>{{ value }} dni</strong>

        <i class="fa fa-plus-circle" @click="next()" :class="{'disabled': !isNext}"></i>
    </div>
</template>

<script>
    export default {
        props: {
            choices: {
                type: Array,
                required: true
            },
            value: {
                type: Number,
                default: 30
            }
        },
        methods: {
            next: function () {
                let index = this._findIndex();

                if (typeof this.choices[index + 1] !== 'undefined') {
                    this.value = this.choices[index + 1];
                    this.$emit('change', this.value);
                }
            },
            prev: function () {
                let index = this._findIndex();

                if (typeof this.choices[index - 1] !== 'undefined') {
                    this.value = this.choices[index - 1];
                    this.$emit('change', this.value);
                }
            },
            _findIndex: function () {
                return this.choices.findIndex(item => item == this.value);
            }
        },
        computed: {
            isNext: function () {
                return typeof this.choices[this._findIndex() + 1] !== 'undefined';
            },
            isPrev: function () {
                return typeof this.choices[this._findIndex() - 1] !== 'undefined';
            }
        }
    }

</script>
