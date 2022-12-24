<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Tag extends Model
{
  use HasFactory;

  /**
   * @todo Define connection of collection.
   */
  protected $connection = 'mongodb';

  /**
   * @todo Define table collection name
   */
  protected $collection = 'Tag';

  /**
   * @todo Declare columns
   */
  protected $fillable = [
    'name',
  ];
}
