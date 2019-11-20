<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stamp extends Model
{
    public $table = 'stamps';

    public $fillable = ['pdf_id' ,'page', 'type','position','created_by','updated_by'];

    public $casts = [
        'position' => 'json',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];


    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    public function updator()
    {
        return $this->belongsTo('App\User', 'updated_by');
    }
}