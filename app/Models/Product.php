<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Product extends Model
{
  use HasFactory;

  /**
   * @todo Define connection of collection.
   */
  protected $connection = 'mongodb';

  /**
   * @todo Define table collection name
   */
  protected $collection = 'Product';

  /**
   * @todo Declare columns
   */
  static $columns = [
    'description',
    'name',
    'gallery',
    'userId',
    'tags',
    'reactions',
    'view',
  ];

  /**
   * @todo Declare columns
   */
  protected $fillable = [
    'description',
    'name',
    'gallery',
    'userId',
    'projectId',
    'tags',
    'reactions',
    'view',
  ];
}
