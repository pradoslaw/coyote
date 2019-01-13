<template>
    <div class="row">
        <div class="col-sm-1 hidden-xs">

        </div>

        <div class="col-sm-11">
            <div class="panel panel-transparent">
                <div class="panel-body comment">
                    <div class="dropdown pull-right">
                        <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="caret"></span>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-right">
                            <li><a href="javascript:" class="btn-edit" :data-id="comment.id"><i class="fa fa-edit fa-fw"></i> Edytuj</a></li>
                            <li><a href="javascript:" :data-target="'#modal-confirm' + comment.id" data-toggle="modal"><i class="fa fa-remove fa-fw"></i> Usuń</a></li>
                        </ul>
                    </div>

                    <ul class="list-inline">
                        <li>
                            <a :href="comment.user.profile" :data-user-id="comment.user.id">{{ comment.user.name }}</a>
                        </li>
                        <li>
                            <a href="javascript:" class="timestamp" :data-timestamp="comment.timestamp">{{ comment.created_at }}</a>
                        </li>
                    </ul>

                    <div class="comment-content">
                        {{ comment.html }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['comment'],
        methods: {

        },
        computed: {

        }
    }

</script>
