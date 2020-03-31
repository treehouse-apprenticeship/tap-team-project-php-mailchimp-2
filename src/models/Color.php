<?php

namespace App\;

class Color extends \Illuminate\Database\Eloquent\Model {

  public function tags() {
    return $this->belongsTo('App\Tag');
  }
}
