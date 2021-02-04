<?php

namespace Drupal\movie\Controller;

use Drupal\Core\Controller\ControllerBase;

class MovieController extends  ControllerBase {
  public function movie_content() {

    $node = \Drupal::entityTypeManager()->getStorage('node');
    $nids = $node->getQuery()->condition('status', 1)->condition('type', 'Movie')->execute();
    $movies = $node->loadMultiple($nids);

    return [
      '#theme' => 'movie_theme_hook',
      '#movies' => $movies,
    ];
  }

  public function movie_reservation_content() {
    $vocabulary = 'Genres';
    $node = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $terms = $node->loadTree($vocabulary);

    foreach ($terms as $term) {
      $genres_data[] = array(
        'id' => $term->tid,
        'name' => $term->name,
      );
    }

    return [
      '#theme' => 'movie_reservation_theme_hook',
      '#genres' => $genres_data,
    ];
  }

}
