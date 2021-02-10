<?php

namespace Drupal\book\Controller;

use Drupal\Core\Controller\ControllerBase;

class BookController extends  ControllerBase
{

  public function fetchBooksData($url) {
    $httpClient = \Drupal::httpClient();

    try {
      $httpRequest = $httpClient->request('GET', $url);
      $content = $httpRequest->getBody()->getContents();
    } catch (GuzzleException $e) {
      \Drupal::logger('http-request-error')->error($e->getMessage());
    }

    if($content) {
      $books = new \SimpleXMLElement($content);
      $entityTypeManager = \Drupal::entityTypeManager()->getStorage('node');
      foreach ($books as $book) {
        $node = $entityTypeManager->create([
          'type' => 'book',
          'title' => $book->title,
          'field_price' => floatval($book->price),
          'field_isbn' => $book->attributes()->ISBN,
          'field_comments' => $book->comments,
        ]);

        $node->save();
      }
    }
  }

  public function getAllBooks() {
    $node = \Drupal::entityTypeManager()->getStorage('node');
    $nids = $node->getQuery()->condition('status', 1)->condition('type', 'Book')->execute();
    $books = $node->loadMultiple($nids);

    return $books;
  }

  public function book_content()
  {
    $books = $this->getAllBooks();
    return [
      '#theme' => 'book_theme_hook',
      '#books' => $books,
    ];
  }
}
