<?php

namespace App;

class Comment extends \Illuminate\Database\Eloquent\Model {
  public function blog() {
    return $this->belongsTo('App\Blog');
  }
}