<template>
    <div class="thumbnail editable-thumbnail">
        <img v-if="url" :src="url">
        <img v-else src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAfQAAADIAQMAAAAk14GrAAAABlBMVEXd3d3///+uIkqAAAAAI0lEQVRoge3BMQEAAADCoPVPbQ0PoAAAAAAAAAAAAAAAAHg2MgAAAYXziG4AAAAASUVORK5CYII=">

        <a v-if="url" class="flush" @click="remove">
            <i class="fa fa-remove fa-2x"></i>
        </a>

        <div v-if="isPending" class="spinner">
            <i class="fa fa-spinner fa-spin fa-2x"></i>
        </div>

        <a v-if="!url && !isPending" class="upload" @click="openDialog">
            <i class="fa fa-plus-circle fa-2x"></i>
        </a>

        <input type="file" ref="input" @change="upload">

        <vue-modal ref="error">
            {{ error }}
        </vue-modal>
    </div>
</template>

<script>
    import axios from 'axios';
    import VueModal from './modal.vue';

    export default {
        props: {
            url: {
                type: String
            },
            file: {
                type: String
            },
            uploadUrl: {
                type: String
            },
            name: {
                type: String,
                default: 'photo'
            }
        },
        components: {
            'vue-modal': VueModal
        },
        data () {
            return {
                isPending: false,
                error: null
            }
        },
        methods: {
            openDialog () {
                this.$refs.input.click();
            },

            upload () {
                let form = new FormData();
                form.append(this.name, this.$refs.input.files[0]);

                this.isPending = true;

                axios.post(this.uploadUrl, form)
                    .then(response => {
                        this.$emit('upload', response.data);
                    })
                    .catch(error => {
                        this.error = error.response.data.message;

                        this.$refs.error.open();

                    })
                    .finally(() => {
                        this.isPending = false;
                    });
            },

            remove () {
                this.$emit('delete', this.file);
            }
        }
    }

</script>
