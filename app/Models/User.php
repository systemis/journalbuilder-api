<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    /**
     * @todo Define connection of collection.
     */
    protected $connection = 'mongodb';

    /**
     * @todo Define table collection name
     */
    protected $collection = 'User';

    /**
     * @todo Declare columns
     */
    static $editable_external_columns = [
      'username',
      'email',
      'given_name',
      'family_name',
      'name',
    ];


    /**
     * @todo Declare columns
     */
    static $editable_columns = [
      'username',
      'email',
      'given_name',
      'family_name',
      'name',
      'description',
      'linkedin',
      'twitter',
      'figma',
      'private',
    ];

    /**
     * @todo Declare columns
     */
    protected $fillable = [
      'username',
      'email',
      'given_name',
      'family_name',
      'name',
      'sub',
      'description',
      'linkedin',
      'twitter',
      'figma',
      'private',
    ];
}
