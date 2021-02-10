<?php

namespace Drupal\movie\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;

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

  public function checkForFullAttendance(EntityInterface $movie) : array {
    $numberOfDays = $movie->field_number_of_attendants->value;
    $disabledDays = [];
    $dbConnection = \Drupal::database();
    foreach ($movie->field_available_on as $day) {
      $query = $dbConnection->select('reservations', 'r');
      $query->condition('day_of_reservation', $day->entity->name->value);
      $query->fields('r', ['id', 'reserved_movie_name'],);
      $result =$query->execute()->fetchAll();
      $num_rows = count($result);

      if ($num_rows >= $numberOfDays) {
        array_push($disabledDays, $day->entity->name->value);
      }
    }
    return $disabledDays;
  }

  public function getAllReservations() : array {
    $dbConnection = \Drupal::database();
    $reservations = [];

    $query = $dbConnection->select('reservations', 'r');
    $query->fields('r', ['day_of_reservation', 'time_of_reservation', 'reserved_movie_genre', 'reserved_movie_name', 'customer_name']);
    $result = $query->execute()->fetchAll();

    foreach($result as $record) {
      array_push($reservations, $record);
    }

    return $reservations;
  }

  public function movie_content() {
    $id = $_GET['id'];

    $movie = $this->getMovieById($id);
    $disabledDays = $this->checkForFullAttendance($movie);

    return [
      '#theme' => 'movie_theme_hook',
      '#movie' => $movie,
      '#disabledDays' => $disabledDays,
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

  public function all_reservations_content() {
    $reservations = $this->getAllReservations();

    return [
      '#theme' => 'all_reservations_theme_hook',
      '#reservations' => $reservations,
    ];
  }

  public function insert_reservations_data() {
    $jsonMovieData = \Drupal::request()->request->get('movieData');

      if($jsonMovieData) {
        $movieData = \GuzzleHttp\json_decode($jsonMovieData);
        $dbConnection = \Drupal::database();

        try {
          $query = $dbConnection->insert('reservations')->fields(
            array(
              'day_of_reservation',
              'time_of_reservation',
              'reserved_movie_genre',
              'reserved_movie_name',
              'customer_name'
            ),
            array(
              $movieData->day,
              \Drupal::time()->getRequestTime(),
              $movieData->genre,
              $movieData->title,
              $movieData->customerName
            )
          )->execute();
        } catch (\Exception $e) {
          \Drupal::logger('confirm-reservation')->error($e->getMessage());
        }
    }
  }
}
