<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// class User extends Authenticatable（変更前）
class User extends Authenticatable implements MustVerifyEmail // （変更後：メール認証用）
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // ユーザーは複数の住所を持つ
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    // ユーザーは複数の購入を持つ
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    // ユーザーは複数のいいねを持つ
    public function likes()
{
    return $this->hasMany(Like::class);
}
}
