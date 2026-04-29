<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleShow extends Model
{
    use HasFactory;
    public function articles()
    {
        return $this->belongsTo(Article::class, 'article_id');
    }
    public function articleshowgallery()
    {
        return $this->hasMany(ArticleShowGallery::class);
    }
    public function phoneNumber() 
    {
        return $this->belongsTo(PhoneNumber::class);
    }
    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    public function traffic()
    {
        return $this->hasMany(Traffic::class);
    }

    public function waTraffic()
    {
        return $this->hasMany(WaTraffic::class);
    }
}
