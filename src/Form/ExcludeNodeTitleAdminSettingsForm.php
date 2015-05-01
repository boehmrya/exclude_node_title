<?php

namespace Drupal\exclude_node_title\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class ExcludeNodeTitleAdminSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'exclude_node_title_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'exclude_node_title.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Display login form:
    $enabled_link = \Drupal::l(t('enabled'), Url::fromRoute('system.modules_list'));
    $form['exclude_node_title_search'] = array(
      '#type' => 'checkbox',
      '#title' => t('Remove node title from search pages'),
      '#description' => t('Select if you wish to remove title from search pages. You need to have Search module !link.', array('!link' => $enabled_link)),
      '#default_value' => _exclude_node_title_var_get('exclude_node_title_search', 0),
      '#disabled' => !\Drupal::moduleHandler()->moduleExists('search'),
    );

    $form['exclude_node_title_content_type'] = array(
      '#type' => 'fieldset',
      '#title' => t('Exclude title by content-types'),
      '#description' => t('Define title excluding settings for each content type.'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );
    $node_types = node_type_get_names();
    foreach ($node_types as $node_type => $node_type_label) {
      $form['#attached']['drupalSettings']['exclude_node_title']['content_types'][$node_type] = $node_type_label;
      $form['exclude_node_title_content_type']['exclude_node_title_content_type_value_' . $node_type] = array(
        '#type' => 'select',
        '#title' => $node_type_label,
        '#default_value' => _exclude_node_title_var_get('exclude_node_title_content_type_value_' . $node_type, 'none'),
        '#options' => array('none' => t('None'), 'all' => t('All nodes...'), 'user' => t('User defined nodes...')),
      );

      $entity_view_modes = \Drupal::entityManager()->getViewModes('node');
      $modes = array();
      foreach ($entity_view_modes as $view_mode_name => $view_mode_info) {
        $modes[$view_mode_name] = $view_mode_info['label'];
      }
      $modes += array('nodeform' => $this->t('Node form'));

      switch ($form['exclude_node_title_content_type']['exclude_node_title_content_type_value_' . $node_type]['#default_value']) {
        case 'all':
          $title = t('Exclude title from all nodes in the following view modes:');
          break;

        case 'user defined':
          $title = t('Exclude title from user defined nodes in the following view modes:');
          break;

        default:
          $title = t('Exclude from:');
      }

      $form['exclude_node_title_content_type']['exclude_node_title_content_type_modes_' . $node_type] = array(
        '#type' => 'checkboxes',
        '#title' => $title,
        '#default_value' => _exclude_node_title_var_get('exclude_node_title_content_type_modes_' . $node_type, array()),
        '#options' => $modes,
        '#states' => array(
          // Hide the modes when the content type value is <none>.
          'invisible' => array(
            'select[name="exclude_node_title_content_type_value_' . $node_type . '"]' => array('value' => 'none'),
          ),
        ),
      );
    }
    $form['#attached']['library'][] = 'exclude_node_title/drupal.exclude_node_title.admin';

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::configFactory()->getEditable('exclude_node_title.settings');

    foreach ($form_state->getValues() as $key => $value) {
      if (Unicode::substr($key, 0, 18) == 'exclude_node_title') {
        $config->set($key, $value);
      }
    }

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
