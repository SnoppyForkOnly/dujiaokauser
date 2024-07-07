<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InviteUser extends Model
{
    protected $guarded = [];
    protected $table = 'invite_user';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invite_user()
    {
        return $this->belongsTo(User::class, 'user_pid', 'id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
