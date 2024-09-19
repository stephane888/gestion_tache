<?php

namespace Drupal\gestion_tache\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AppMemosTypeForm.
 */
class AppMemosTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $app_memos_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $app_memos_type->label(),
      '#description' => $this->t("Label for the App memos type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $app_memos_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\gestion_tache\Entity\AppMemosType::load',
      ],
      '#disabled' => !$app_memos_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $app_memos_type = $this->entity;
    $status = $app_memos_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label App memos type.', [
          '%label' => $app_memos_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label App memos type.', [
          '%label' => $app_memos_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($app_memos_type->toUrl('collection'));
  }

}
