<?php
/* updated namespace to include 'model'
Added 'use' for Eloquent*/
namespace App\models;
use Illuminate\Database\Eloquent\Model;
class Color extends \Illuminate\Database\Eloquent\Model {

  public function tags() {
    return $this->belongsTo('App\models\Tag');
  }
}
