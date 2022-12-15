<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class User extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'User';
    protected $fillable = [
      'username',
      'email',
      'given_name',
      'family_name',
      'name'
    ];
}
