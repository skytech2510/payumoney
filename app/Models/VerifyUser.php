<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VerifyUser extends Model
{
    protected $table    = 'verify_users';
    protected $fillable = ['user_id', 'token'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Store Verify User
     *
     * @param int $userId
     * @return void
     */
    public function createVerifyUser($userId)
    {
        self::firstOrCreate(
            ['user_id' => $userId],
            ['user_id' => $userId, 'token' => Str::random(40)]
        );

    }
    
    /**
     * generateVerificationToken
     *
     * @param  int $userId
     * @return void
     */
    public static function generateVerificationToken(int $userId): void
    {
        self::firstOrCreate(
            ['user_id' => $userId],
            ['token' => Str::random(40)]
        );
    }
}
