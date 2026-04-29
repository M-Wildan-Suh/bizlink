<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaTraffic extends Model
{
    use HasFactory;
    protected $fillable = ['article_show_id', 'guardian_web_id', 'access'];
}
