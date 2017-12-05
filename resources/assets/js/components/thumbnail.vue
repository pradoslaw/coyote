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
    </div>
</template>

<script>
    import Dialog from '../libs/dialog';

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
            }
        },
        data: function() {
            return {
                isPending: false
            }
        },
        methods: {
            openDialog: function () {
                this.$refs.input.click();
            },
            upload: function () {
                let form = new FormData();
                form.append('photo', this.$refs.input.files[0]);

                this.isPending = true;

                $.ajax({
                    url: this.uploadUrl,
                    type: 'POST',
                    data: form,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: (result) => {
                        this.$emit('upload', result);
                    },
                    complete: () => {
                        this.isPending = false;
                    },
                    error: function (err) {
                        if (typeof err.responseJSON !== 'undefined') {
                            Dialog.alert({message: err.responseJSON.photo[0]}).show();
                        }
                    }
                }, 'json');
            },
            remove: function () {
                this.$emit('delete', this.file);
            }
        }
    }

</script>
