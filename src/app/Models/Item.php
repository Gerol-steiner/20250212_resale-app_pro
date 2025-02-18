<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image_url',
        'condition_id',
        'name',
        'brand',
        'description',
        'price',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'item_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'item_category'); // 中間テーブルを指定
    }

    public function condition()
    {
        return $this->belongsTo(Condition::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'item_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'item_id');
    }

    public function chats(): HasManyThrough
    {
        return $this->hasManyThrough(Chat::class, Purchase::class, 'item_id', 'purchase_id', 'id', 'id');
    }
}
