<?php
namespace Coyote;

use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $partner
 * @property int $system
 */
class Group extends Eloquent\Model
{
    protected $fillable = ['name', 'description', 'user_id', 'partner'];
    protected $dateFormat = 'Y-m-d H:i:se';

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_users');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'group_permissions')->withPivot('value');
    }

    public function shortName(): ?string
    {
        if ($this->name === null) {
            return null;
        }
        return self::groupShortName($this->name);
    }

    public static function groupShortName(string $groupName): string
    {
        if (\str_contains($groupName, ' ')) {
            return \strStr(\lTrim($groupName), ' ', before_needle:true);
        }
        return $groupName;
    }
}
