<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    //use HasFactory;

    protected $fillable = [
        'image',
        'caption',
        'user_id',
    ];

    /**
     * Get the user that owns the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createPost(array $data)
    {
        $post = new Post();
        $post->title = $data['title'] ?? null;
        $post->content = $data['content'] ?? null;
        $post->save();
        return $post;
    }
}

