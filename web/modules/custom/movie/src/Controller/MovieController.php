<?php

namespace Drupal\movie\Controller;

use Drupal\Core\Controller\ControllerBase;

class MovieController extends  ControllerBase {

  public function getMovies() {
    $node = \Drupal::entityTypeManager()->getStorage('node');
    $nids = $node->getQuery()->condition('status', 1)->condition('type', 'Movie')->execute();
    $movies = $node->loadMultiple($nids);

    return $movies;
  }

  public function movie_content() {

    $movies = $this->getMovies();

    return [
      '#theme' => 'movie_theme_hook',
      '#movies' => $movies,
    ];
  }

  public function movie_reservation_content() {

    $vocabulary = 'Genres';
    $taxonomy = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $terms = $taxonomy->loadTree($vocabulary);
    foreach ($terms as $term) {
      $genres_data[] = array(
        'id' => $term->tid,
        'name' => $term->name,
      );
    }

    $selectedGenre = \Drupal::request()->request->get('genres') ? \Drupal::request()->request->get('genres') : 'Action';

    $movies = $this->getMovies();

    return [
      '#theme' => 'movie_reservation_theme_hook',
      '#movies' => $movies,
      '#genres' => $genres_data,
      '#selectedGenre' => $selectedGenre,
    ];
  }
}
