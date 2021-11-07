<?php

namespace Coyote\Guide;

use Coyote\Guide;
use Coyote\Models\Scopes\ForUser;
use Coyote\User;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use ForUser;

    const JUNIOR = 'Junior';
    const MIDDLE = 'Mid-Level';
    const SENIOR = 'Senior';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['guide_id', 'user_id', 'role'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'guide_roles';

    /**
     * @var array
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function guide()
    {
        return $this->belongsTo(Guide::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
