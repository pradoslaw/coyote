<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Reputation extends Model
{
    const POST_VOTE = 1;
    const POST_ACCEPT = 2;
    const MICROBLOG = 3;
    const MICROBLOG_VOTE = 4;
    const WIKI_CREATE = 5;
    const WIKI_EDIT = 6;
    const CUSTOM = 7;
    const WIKI_RATE = 8;
}
