<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuardianWeb extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'url',
        'use_cpanel',
        'cpanel_account_id',
        'cpanel_domain_type',
        'cpanel_domain_created_at',
    ];

    protected $casts = [
        'use_cpanel' => 'boolean',
        'cpanel_domain_created_at' => 'datetime',
    ];

    public function articles(){
        return $this->hasMany(Article::class);
    }

    public function traffic() {
        return $this->hasMany(Traffic::class);
    }

    public function waTraffic() {
        return $this->hasMany(WaTraffic::class);
    }

    public function cpanelAccount()
    {
        return $this->belongsTo(CpanelAccount::class);
    }
}
