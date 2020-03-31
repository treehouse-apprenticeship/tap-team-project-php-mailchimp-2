<?php

namespace App\models;
use Cocur\Slugify\Slugify;


class Blog extends \Illuminate\Database\Eloquent\Model {
  public function comments() {
    return $this->hasMany('App\models\Comment');
  }

  public function tags() {
    return $this->belongsToMany('App\models\Tag');
  }
}