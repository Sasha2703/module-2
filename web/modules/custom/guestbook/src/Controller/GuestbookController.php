<?php

namespace Drupal\guestbook\Controller;

/**
 * @file
 * Provides database creating functionality.
 */

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;

/**
 * Provides content.
 */
class GuestbookController extends ControllerBase {

  /**
   * Implements content().
   */
  public function content() {
    $guestbook['book_form'] = \Drupal::formBuilder()
      ->getForm('Drupal\guestbook\Form\BookForm');
    $query = \Drupal::database()->select('responses', 'r');
    $query->fields('r', [
      'id',
      'author_name',
      'email',
      'phone',
      'avatar',
      'image,',
      'message',
      'timestamp',
    ])->orderBy('timestamp', 'desc');
    $data = $query->execute()->fetchAll();
    $responses = [];

    foreach ($data as $field) {
      if (!$field->avatar == 0) {
        $file_avatar = File::load($field->avatar);
        $avatar_uri = $file_avatar->getFileUri();
        $avatar_is_set = TRUE;
      }
      else {
        $avatar_uri = '/modules/custom/guestbook/images/default_user.png';
        $avatar_is_set = FALSE;
      }
      if (!$field->image == 0) {
        $file_image = File::load($field->image);
        $image_uri = $file_image->getFileUri();
      }
      else {
        $image_uri = 0;
      }

      $avatar_img = [
        '#theme' => 'image_style',
        '#style_name' => 'wide',
        '#uri' => $avatar_uri,
        '#title' => 'avatar',
        '#width' => 50,
        '#height' => 50,
        '#isset' => $avatar_is_set,
      ];
      $image_img = [
        '#theme' => 'image_style',
        '#style_name' => 'wide',
        '#uri' => $image_uri,
        '#title' => 'image',
        '#width' => 150,
        '#height' => 150,
      ];
      $responses[] = [
        'id' => $field->id,
        'name' => $field->author_name,
        'email' => $field->email,
        'phone' => $field->phone,
        'message' => $field->message,
        'avatar' => $avatar_img,
        'image' => $image_img,
        'created_time' => $field->timestamp,
      ];
    }

    $build = [
      '#theme' => 'guestbook-template',
      '#responses' => $responses,
      '#form' => $guestbook,
    ];
    return $build;
  }

}
