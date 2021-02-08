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

  public function getMovieById($id) {
    $node = \Drupal::entityTypeManager()->getStorage('node');
    $nids = $node->getQuery()->condition('status', 1)->condition('type', 'Movie')->execute();
    foreach ($nids as $nid) {
      if($nid == $id) {
        $selectedId = $nid;
      }
    }
    $movie = $node->load($selectedId);

    return $movie;
  }

  public function movie_content() {
    $id = $_GET['id'];

    $movie = $this->getMovieById($id);
    return [
      '#theme' => 'movie_theme_hook',
      '#movie' => $movie,
    ];
  }

  public function movie_reservation_content() {
    $this->insert_reservations_data();

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

  public function insert_reservations_data() {
    $customerName = \Drupal::request()->request->get('customerName');
    $day = \Drupal::request()->request->get('day');
    $movieName = \Drupal::request()->get('movieName');
    $movieGenre = \Drupal::request()->get('genre');

    if($movieGenre != NULL && $movieName != NULL && $customerName != NULL && $day != NULL) {
      $conn = \Drupal::database();
    try {
      $query = $conn->insert('reservations')->fields(
        array(
          'day_of_reservation',
          'time_of_reservation',
          'reserved_movie_genre',
          'reserved_movie_name',
          'customer_name'
        ),
        array(
          $day,
          \Drupal::time()->getRequestTime(),
          $movieGenre,
          $movieName,
          $customerName
        )
      )->execute();
    } catch (\Exception $e) {
      \Drupal::logger('confirm-reservation')->error($e->getMessage());
    }
  }
  }

}
