<?php

namespace App\models;

class Tag extends \Illuminate\Database\Eloquent\Model {
  public function blogs() {
    return $this->belongsToMany('App\models\Blog');
  }

  public function color() {
    return $this->hasOne('App\models\Color');
  }
}