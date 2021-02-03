<?php

namespace Drupal\movie\Controller;

use Drupal\Core\Controller\ControllerBase;

class MovieController extends  ControllerBase {
  public function content() {

    $node = \Drupal::entityTypeManager()->getStorage('node');
    $nids = $node->getQuery()->condition('status', 1)->condition('type', 'Movie')->execute();
    $movies = $node->loadMultiple($nids);

    return [
      '#theme' => 'movie_theme_hook',
      '#movies' => $movies,
    ];

  }

}
