<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Comment;

class Annotation extends Model
{
    public $table = 'annotations';

    public $fillable = ['pdf_id' ,'page', 'properties', 'quote', 'ranges', 'shapes', 'created_by', 'updated_by'];

    public $casts = [
        'ranges' => 'array',
        'shapes' => 'array',
        'properties' => 'json'
    ];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }


    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    public function updator()
    {
        return $this->belongsTo('App\User', 'updated_by');
    }
}