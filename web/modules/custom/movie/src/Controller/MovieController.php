<?php

namespace Drupal\movie\Controller;

use Drupal\Core\Controller\ControllerBase;

class MovieController extends  ControllerBase {
  public function content() {

    $myTitle = "Lord of the Rings";
    $myDescription = "Movie description";

    return [
      '#theme' => 'movie_theme_hook',
      '#movieTitle' => $myTitle,
      '#movieDesc' => $myDescription,
    ];

  }

}
