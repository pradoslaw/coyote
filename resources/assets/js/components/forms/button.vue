<template>
    <button :disabled="disabled">
        <i v-if="disabled" class="fa fa-spinner fa-spin fa-fw"></i>

        <slot></slot>
    </button>
</template>

<script>
    export default {
        props: {
            disabled: {
                type: Boolean,
                default: false
            }
        }
    }
</script>
