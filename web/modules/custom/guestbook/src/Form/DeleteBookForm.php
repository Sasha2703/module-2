<?php

namespace Drupal\guestbook\Form;

/**
 * @file
 * Provides functionality.
 */
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides content.
 */
class DeleteBookForm extends ConfirmFormBase {

  /**
   * Return Question.
   */
  public function getQuestion() {
    return $this->t('Do you want to delete this response?');
  }

  /**
   * Return URL if cancel.
   */
  public function getCancelUrl() {
    return new Url('guestbook.content');
  }

  /**
   * Return Description.
   */
  public function getDescription() {
    return $this->t('Do you want to delete?');
  }

  /**
   * Return confirm text.
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * Return cancel text.
   */
  public function getCancelText() {
    return t('Cancel');
  }

  /**
   * Return FormID.
   */
  public function getFormId() {
    return 'Delete Response';
  }

  /**
   * Return form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $bookID = NULL) {
    $this->id = $bookID;
    return parent::buildForm($form, $form_state);
  }

  /**
   * Validate form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * Submit form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $query = \Drupal::database();
    $query->delete('responses')
      ->condition('id', $this->id)
      ->execute();
    \Drupal::messenger()->addStatus('You deleted the response');
    $form_state->setRedirect('guestbook.content');
  }

}
