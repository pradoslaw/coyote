<template>
    <div :id="'comment-' + comment.id" class="media" :class="comment.parent_id ? 'indent' : ''">

        <div class="media-left">
            <img :src="comment.user.photo" class="img-thumbnail media-object">
        </div>

        <div class="media-body">
            <div class="dropdown pull-right">
                <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="caret"></span>
                </button>

                <ul class="dropdown-menu dropdown-menu-right" v-if="comment.editable">
                    <li><a @click="edit" href="javascript:" class="btn-edit" :data-id="comment.id"><i class="fa fa-edit fa-fw"></i> Edytuj</a></li>
                    <li><a v-on:click="remove" href="javascript:" :data-target="'#modal-confirm' + comment.id" data-toggle="modal"><i class="fa fa-remove fa-fw"></i> Usuń</a></li>
                </ul>
            </div>

            <div class="media-heading">
                <h5><a :href="comment.user.profile" :data-user-id="comment.user.id">{{ comment.user.name }}</a></h5>

                <h6><a :href="'#comment-' + comment.id" class="text-muted timestamp" :data-timestamp="comment.timestamp">{{ comment.created_at }}</a></h6>
            </div>

            <div class="margin-sm-top margin-sm-bottom" v-if="!isEditing">
                {{ comment.html }}
            </div>

            <div class="margin-sm-top" v-if="isEditing">
                <form method="post" :action="comment.route.edit" v-on:submit.prevent="submitForm">
                    <div class="form-group">
                        <textarea name="text" class="form-control">{{ comment.text}}</textarea>
                    </div>

                    <div class="form-group">
                        <input type="submit" class="btn btn-primary btn-sm pull-right">
                    </div>
                </form>
            </div>

            <ul class="list-inline">
                <li><a href="#" class="text-muted">Odpowiedz</a></li>
                <li><a href="#" class="text-muted">Zgłoś</a></li>
            </ul>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        props: ['comment'],
        data: () => {
            return {
                isEditing: false
            }
        },
        methods: {
            edit: function () {
                this.isEditing = !this.isEditing;
            },

            remove: function () {
                axios.delete(this.comment.route.delete).then(() => {
                    this.comment.deleted_at = new Date();
                });
            },

            submitForm: function (e) {
                axios.post(e.target.action, new FormData(e.target))
                    .then(response => {
                        this.comment = response.data;
                        this.isEditing = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            }
        },
        computed: {

        }
    }

</script>
