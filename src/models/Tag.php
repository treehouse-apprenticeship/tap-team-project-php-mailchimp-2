<?php

namespace App;

class Tag extends \Illuminate\Database\Eloquent\Model {
  public function blogs() {
    return $this->belongsToMany('App\Blog');
  }

  public function color() {
    return $this->hasOne('App\Color');
  }
}