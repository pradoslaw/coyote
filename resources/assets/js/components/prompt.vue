<template>
    <div>
        <slot></slot>

        <ul class="auto-complete" v-show="shouldDisplayList"></ul>
    </div>
</template>

<script>
    export default {
        prop: ['source'],
        data() {
            return {
                shouldDisplayList: false
            }
        },
        mounted() {
            const $el = this.$slots.default[0].elm;

            console.log($el);

            $el.addEventListener('keydown', this._onKeyDown);
        },
        methods: {
            _onKeyDown: function () {
                console.log('keydown');
            }
        }
    }
</script>
