<?php

namespace Drupal\guestbook\Form;

/**
 * @file
 * Provides functionality.
 */

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

/**
 * Provides content.
 */
class BookForm extends FormBase {

  /**
   * Implements content().
   */
  public function getFormId() {
    return 'response_form';
  }

  /**
   * Implements content().
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your Name:'),
      '#description' => $this->t('Enter your name. Note that name must be longer than 2 characters and shorter than 100 characters'),
      '#maxlength' => 100,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::ajaxValidateNameLength',
        'event' => 'change',
      ],
    ];
    $form['mail'] = [
      '#type' => 'email',
      '#title' => $this->t('Your Email:'),
      '#description' => $this->t('Email must looks like "text@text.text"'),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::ajaxValidateEmailFormat',
        'event' => 'change',
      ],
    ];
    $form['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Your Phone:'),
      '#description' => $this->t('Phone must be "+380XXXXXXXXX" format'),
      '#required' => TRUE,
      '#placeholder' => $this->t('+380XXXXXXXXX'),
      '#default_value' => ' ',
      '#maxlength' => 13,
      '#ajax' => [
        'callback' => '::ajaxValidatePhoneFormat',
        'event' => 'change',
      ],
    ];
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Your Message:'),
      '#rows' => 5,
      '#cols' => 15,
      '#description' => $this->t('Entry your message'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];
    $form['avatar'] = [
      '#type' => 'managed_file',
      '#name' => 'avatar',
      '#title' => $this->t('Your Avatar Image:'),
      '#upload_location' => 'public://avatar',
      '#description' => $this->t('The file must be .jpeg, .jpg or .png format and less than 2MB.'),
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [2 * 1024 * 1024],
      ],
    ];
    $form['image'] = [
      '#type' => 'managed_file',
      '#name' => 'image',
      '#title' => $this->t('Message Image:'),
      '#upload_location' => 'public://image',
      '#description' => $this->t('The file must be .jpeg, .jpg or .png format and less than 5MB.'),
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [5 * 1024 * 1024],
      ],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#name' => 'submit',
      '#value' => $this->t('SEND MESSAGE'),
      '#ajax' => [
        'callback' => '::ajaxSubmitMessage',
        'event' => 'click',
      ],
    ];

    return $form;

  }

  /**
   * Function that validate Name field on its length.
   */
  public function validateNameLength(array &$form, FormStateInterface $form_state) {
    $name_len = strlen($form_state->getValue('name'));
    if ($name_len <= 2) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Function that validate Name field on its length with Ajax.
   */
  public function ajaxValidateNameLength(array &$form, FormStateInterface $form_state) {
    $isNameValid = $this->validateNameLength($form, $form_state);
    $response = new AjaxResponse();

    if ($isNameValid) {
      $name_css = ['border' => '1px solid green'];
    }
    else {
      $name_css = ['border' => '1px solid red'];
    }
    $response->addCommand(new CssCommand('#edit-name', $name_css));

    return $response;
  }

  /**
   * Function that validate Email field.
   */
  public function validateEmailFormat(array &$form, FormStateInterface $form_state) {
    if (filter_var($form_state->getValue('mail'), FILTER_VALIDATE_EMAIL)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Function that validate Email field with AJAX.
   */
  public function ajaxValidateEmailFormat(array &$form, FormStateInterface $form_state) {
    $valid = $this->validateEmailFormat($form, $form_state);
    $response = new AjaxResponse();

    if ($valid) {
      $css = ['border' => '1px solid green'];
    }
    else {
      $css = ['border' => '1px solid red'];
    }
    $response->addCommand(new CssCommand('#edit-mail', $css));

    return $response;
  }

  /**
   * Function that validate Phone field.
   */
  public function validatePhoneFormat(array &$form, FormStateInterface $form_state) {
    $phone = $form_state->getValue('phone');
    if (preg_match("/[+]380[0-9]{7}/", $phone)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Function that validate Phone field with AJAX.
   */
  public function ajaxValidatePhoneFormat(array &$form, FormStateInterface $form_state) {
    $valid = $this->validatePhoneFormat($form, $form_state);
    $response = new AjaxResponse();

    if ($valid) {
      $css = ['border' => '1px solid green'];
    }
    else {
      $css = ['border' => '1px solid red'];
    }
    $response->addCommand(new CssCommand('#edit-phone', $css));

    return $response;
  }

  /**
   * Function that validate Form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $name_len = strlen($form_state->getValue('name'));
    $error_count = 0;
    if (!$this->validateNameLength($form, $form_state)) {
      $form_state->setErrorByName('name', $this->t('✗ Name is too short.' . $name_len));
      $error_count++;
    }
    if (!$this->validateEmailFormat($form, $form_state)) {
      $form_state->setErrorByName('email', $this->t('✗ Email is not valid'));
      $error_count++;
    }
    if (!$this->validatePhoneFormat($form, $form_state)) {
      $form_state->setErrorByName('phone', $this->t('✗ Enter the phone number correctly'));
      $error_count++;
    }
    if ($error_count > 0) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Implements content().
   */
  public function ajaxSubmitMessage(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $url = Url::fromRoute('guestbook.content');

    $response->addCommand(new RedirectCommand($url->toString()));
    if ($this->validateForm($form, $form_state)) {
      $response->addCommand(new MessageCommand($this->t('✓ Your message added')));
    }
    return $response;
  }

  /**
   * Implements content().
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $avatar = $form_state->getValue('avatar');
    $image = $form_state->getValue('image');
    $current_date = date('m/d/y h:i:s', strtotime('+3 hour'));

    if ($this->validateForm($form, $form_state)) {
      if (!is_null($avatar[0])) {
        $file_avatar = File::load($avatar[0]);
        $file_avatar->setPermanent();
        $file_avatar->save();
      }
      else {
        $avatar[0] = 0;
      }
      if (!is_null($image[0])) {
        $file_image = File::load($image[0]);
        $file_image->setPermanent();
        $file_image->save();
      }
      else {
        $image[0] = 0;
      }

      $book = [
        'author_name' => $form_state->getValue('name'),
        'email' => $form_state->getValue('mail'),
        'phone' => $form_state->getValue('phone'),
        'message' => $form_state->getValue('message'),
        'avatar' => $avatar[0],
        'image' => $image[0],
        'timestamp' => $current_date,
      ];

      \Drupal::database()->insert('responses')->fields($book)->execute();
    }

  }

}
