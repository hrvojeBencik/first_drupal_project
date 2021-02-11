<?php

namespace Drupal\movie\Controller;

use Drupal\Core\Condition\ConditionInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Query\Query;
use Drupal\Core\Database\Query\SelectInterface;
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

  public function checkForUrlParameters(SelectInterface $query) {
    $getRequest = \Drupal::request();
    $movieData = [
      'day_of_reservation' => $getRequest->get('day'),
      'reserved_movie_genre' =>  $getRequest->get('genre'),
      'reserved_movie_name' => $getRequest->get('movieTitle'),
      'customer_name' =>  $getRequest->get('name'),
    ];

    foreach ($movieData as $key => $value) {
      if($value) {
        $query->condition($key, str_replace('+', ' ', $value));
      }
    }

    return $query;
  }

  public function getAllReservations($sort) : array {
    $dbConnection = \Drupal::database();
    $reservations = [];
    $defaultSort = 'reserved_movie_name';
    $defaultSortDirection = 'ASC';

    if($sort === 'Z-A') {
      $defaultSortDirection = 'DESC';
    } elseif ($sort === 'Newest') {
      $defaultSort = 'time_of_reservation';
      $defaultSortDirection = 'DESC';
    } elseif ($sort === 'Oldest') {
      $defaultSort = 'time_of_reservation';
      $defaultSortDirection = 'ASC';
    }

    $query = $dbConnection->select('reservations', 'r');
    $query = $this->checkForUrlParameters($query);
    $query->fields('r', ['day_of_reservation', 'time_of_reservation', 'reserved_movie_genre', 'reserved_movie_name', 'customer_name'])
          ->orderBy($defaultSort, $defaultSortDirection);
    $reservations = $query->execute()->fetchAll();

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
    \Drupal::service('page_cache_kill_switch')->trigger();
    $sort = \Drupal::request()->request->get('sort') ?? 'A-Z';
    $reservations = $this->getAllReservations($sort);

    return [
      '#theme' => 'all_reservations_theme_hook',
      '#reservations' => $reservations,
      '#selectedSort' => $sort,
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
