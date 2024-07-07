<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;





class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
   protected $fillable = ['email', 'password', 'last_ip', 'last_login', 'register_at', 'pid', 'invite_code','grade', 'money'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function invite_user()
    {
        return $this->belongsTo(self::class, 'pid', 'id');
    }
    
      
    

}
