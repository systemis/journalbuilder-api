<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Feedback extends Model
{
  use HasFactory;

  /**
   * @todo Define connection of collection.
   */
  protected $connection = 'mongodb';

  /**
   * @todo Define table collection name
   */
  protected $collection = 'Feedback';

  /**
   * @todo Declare columns
   */
  static $columns = [
    'productId',
    'userId',
    'message',
  ];

  /**
   * @todo Declare columns
   */
  protected $fillable = [
    'productId',
    'userId',
    'message',
  ];
}
