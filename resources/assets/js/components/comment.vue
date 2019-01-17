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

                <ul class="dropdown-menu dropdown-menu-right">
                    <li><a @click="edit" href="javascript:" class="btn-edit" :data-id="comment.id"><i class="fa fa-edit fa-fw"></i> Edytuj</a></li>
                    <li><a v-on:click="remove" href="javascript:" :data-target="'#modal-confirm' + comment.id" data-toggle="modal"><i class="fa fa-remove fa-fw"></i> Usuń</a></li>
                </ul>
            </div>

            <div class="media-heading">
                <h5><a :href="comment.user.profile" :data-user-id="comment.user.id">{{ comment.user.name }}</a></h5>

                <h6><a :href="'#comment-' + comment.id" class="text-muted timestamp" :data-timestamp="comment.timestamp">{{ comment.created_at }}</a></h6>
            </div>

            <div class="comment-content margin-sm-top">
                {{ comment.html }}

                <!--<form method="post" v-on:submit.prevent="submitForm">-->
                    <!--<textarea name="text">{{ comment.text}}</textarea>-->
                <!--</form>-->
            </div>

            <ul class="list-unstyled">
                <li><a href="#" class="text-muted">Odpowiedz</a></li>
            </ul>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['comment'],
        methods: {
            edit: function () {

            },

            remove: function () {

            }
        },
        computed: {

        }
    }

</script>
