<?php

namespace App;
use Cocur\Slugify\Slugify;


class Blog extends \Illuminate\Database\Eloquent\Model {
  public function comments() {
    return $this->hasMany('App\Comment');
  }

  public function tags() {
    return $this->belongsToMany('App\Tag');
  }
}