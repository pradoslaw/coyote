<template>
    <span v-if="message" class="help-block">{{ errorMessage }}</span>
</template>

<script>
    export default {
        props: {
            message: {
                type: [String, Array]
            }
        },
        computed: {
            errorMessage () {
                if (Array.isArray(this.message)) {
                    return this.message[0];
                }

                return this.message;
            }
        }
    };
</script>
