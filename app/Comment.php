<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\User;

class Comment extends Model
{
    public $table = 'comments';

    public $fillable = ['annotation_id' ,'text', 'user_id'];

    /**
     * The belongs to Relationship
     *
     * @var array
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}