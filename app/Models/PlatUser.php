<?php

namespace App\Models;

use App\Models\Observer\PlatUserObserver;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class PlatUser extends Model implements Transformable
{
    use TransformableTrait;

    protected $table = 'plat_users';
    protected $hidden = ['password'];
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub
        PlatUser::observe(PlatUserObserver::class);
    }

    public function apps()
    {
        return $this->hasMany(PlatUserApp::class, 'uid');
    }

    public function upper()
    {
        return $this->belongsTo(PlatUser::class, 'upper_id');
    }

    public function scopeProxy($query)
    {
        return $query->where('role', 1);
    }

    public function scopeAudited($query)
    {
        return $query->where('status', 1);
    }

    public function profile()
    {
        return $this->hasOne(PlatUserProfile::class, 'uid');
    }

    public function getLastAtAttribute($last_at)
    {
        return date('Y-m-d H:i:s', $last_at);
    }

    public function getLastIpAttribute($ip)
    {
        return long2ip($ip);
    }

    public function setLastIpAttribute($ip)
    {
        $this->attributes['last_ip'] = ip2long($ip);
    }

    public function assets()
    {
        return $this->hasOne(AssetCount::class, 'uid');
    }
}
