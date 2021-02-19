<?php

namespace Drupal\subscription\Controller;

use Drupal\Core\Controller\ControllerBase;

class SubscriptionController extends ControllerBase {

  public function subscription_content() {
    $this->insert();

    return [
      '#theme' => 'subscription_theme_hook',
    ];
  }

  public function subscription_form_content() {

    return [
      '#theme' => 'subscription_form_theme_hook',
    ];
  }

  public function insert() {
    $jsonUserData = \Drupal::request()->request->get('userData');

    if($jsonUserData) {
      $userData = \GuzzleHttp\json_decode($jsonUserData);
      $dbConnection = \Drupal::database();

      try {
        $query = $dbConnection->select('subscriptions', 's');
        $query->condition('email_address', $userData->email);
        $query->fields('s', ['id', 'first_name', 'last_name'],);
        $result = $query->execute()->fetchAll();

        if(count($result) === 0) {
        $query = $dbConnection->insert('subscriptions')->fields(
          array(
            'first_name',
            'last_name',
            'email_address',
            'phone_number',
            'country',
            'city',
            'gender',
          ),
          array(
            $userData->first_name,
            $userData->last_name,
            $userData->email,
            $userData->phone_number,
            $userData->country,
            $userData->city,
            $userData->gender,
          )
        )->execute();
          header('Content-Type: application/json');
          $data = json_encode(array('success' => true));
          echo $data;
        } else {
          header('Content-Type: application/json');
          $data = json_encode(array('success' => false));
          echo $data;
        }
      } catch (\Exception $e) {
        \Drupal::logger('confirm-subscription')->error($e->getMessage());
        echo json_encode(array('success' => false));
      }
    }

  }
}
