<?php

namespace App\Models;

use App\Helpers;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Storage;

/**
 * App\Models\User
 *
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property string $apikey
 * @property string $password
 * @property boolean $admin
 * @property boolean $enabled
 * @property boolean $banned
 * @property string $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Upload[] $uploads
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereApikey($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User wherePassword($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereAdmin($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereEnabled($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereBanned($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereRememberToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \App\Models\UserPreferences $preferences
 */
class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'apikey', 'password', 'enabled', 'banned', 'admin'];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'apikey', 'enabled', 'banned', 'admin'];

    /**
     * Get this user's uploads.
     */
    public function uploads()
    {
        return $this->hasMany('App\Models\Upload');
    }

    public function preferences()
    {
        return $this->hasOne('App\Models\UserPreferences');
    }

    public function forceDelete()
    {
        if (Storage::exists('uploads/' . md5($this->id))) {
            Storage::deleteDirectory('uploads/' . md5($this->id));
        }

        if (Storage::exists('thumbnails/' . md5($this->id))) {
            Storage::deleteDirectory('thumbnails/' . md5($this->id));
        }

        Helpers::invalidateCache();

        return parent::forceDelete();
    }

    public function isPrivilegedUser()
    {
        return $this->admin || $this->isSuperUser();
    }

    public function isSuperUser()
    {
        return $this->id === 1;
    }
}
