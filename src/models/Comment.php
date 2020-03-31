<?php

namespace App\models;

class Comment extends \Illuminate\Database\Eloquent\Model {
  public function blog() {
    return $this->belongsTo('App\models\Blog');
  }
}