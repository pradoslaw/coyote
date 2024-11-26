<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $partner
 * @property int $system
 */
class Group extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'user_id', 'partner'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_users');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany('Coyote\Permission', 'group_permissions')->withPivot('value');
    }

    public function shortName(): ?string
    {
        if ($this->name === null) {
            return null;
        }
        if (\str_contains($this->name, ' ')) {
            return \strStr(\lTrim($this->name), ' ', before_needle:true);
        }
        return $this->name;
    }
}
