<?php

namespace Drupal\movie\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class ExportForm extends FormBase {
  public function getFormId()
  {
    return 'export_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['select_format'] = array(
      '#type' => 'select',
      '#title' => 'Select export format of your choice: <br>',
      '#options' => array(
        'csv' => t('CSV'),
        'xml' =>t('XML'),
      ),
    );

    $from['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t("Submit"),
      '#button_type' => 'primary',
    );

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('select_format') !== 'csv' && $form_state->getValue('select_format') !== 'xml') {
      $form_state->setErrorByName('select_format', $this->t('Please choose format.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $dbConnection = \Drupal\Core\Database\Database::getConnection();
    foreach ($form_state->getValues() as $key => $value) {
      if($value === "csv" || $value === "xml") {
        $query = "SELECT 'Movie Title', 'Movie Description', 'Image Title', 'Movie Genre', 'Available On'
                  UNION ALL
                  ( SELECT node.title AS movie_title, description.field_description_value AS description, image.field_image_title AS image_title, genre.name AS genre, days.name AS days_available
                  FROM node_field_data AS node
                  JOIN node__field_include_in_exporter AS include ON node.vid = include.revision_id
                  JOIN node__field_description AS description ON node.vid = description.revision_id
                  JOIN node__field_image AS image ON node.vid = image.revision_id
                  JOIN node__field_genre AS genreId ON node.vid = genreId.revision_id
                  JOIN taxonomy_term_field_data AS genre ON genre.revision_id = genreId.field_genre_target_id
                  JOIN node__field_available_on as daysId ON node.vid = daysId.revision_id
                  JOIN taxonomy_term_field_data AS days ON days.revision_id = daysId.field_available_on_target_id)
                  INTO OUTFILE 'C:/tmp/db.{$value}'
                  FIELDS TERMINATED BY ','
                  ENCLOSED BY '\"'
                  LINES TERMINATED BY '\n\r';";
        $dbConnection->query($query);
        break;
      }
    }
  }
}
