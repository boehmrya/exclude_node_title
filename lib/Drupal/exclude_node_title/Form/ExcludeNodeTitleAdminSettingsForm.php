<?php

namespace Drupal\exclude_node_title\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
  public function buildForm(array $form, array &$form_state) {
    // Display login form:
    $form['exclude_node_title_search'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Remove node title from search pages'),
      '#description' => $this->t('Select if you wish to remove title from search pages. You need to have Search module !url.', array('!url' => l(t('enabled'), 'admin/modules/list'))),
      '#default_value' => _exclude_node_title_var_get('exclude_node_title_search', 0),
      '#disabled' => !module_exists('search')
    );

    $form['exclude_node_title_content_type'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Exclude title by content-types'),
      '#description' => $this->t('Define title excluding settings for each content type.'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );
    $node_types = node_type_get_names();
    foreach ($node_types as $node_type => $node_type_label) {
      $form['#attached']['js'][] = array(
        'data' => array(
          'exclude_node_title' => array(
            'content_types' => array(
              $node_type => $node_type_label,
            ),
          ),
        ),
        'type' => 'setting',
      );
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
          $title = 'Exclude title from all nodes in the following view modes:';
          break;

        case 'user defined':
          $title = 'Exclude title from user defined nodes in the following view modes:';
          break;

        default:
          $title = 'Exclude from:';
      }

      $form['exclude_node_title_content_type']['exclude_node_title_content_type_modes_' . $node_type] = array(
        '#type' => 'checkboxes',
        '#title' => $this->t($title),
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
    $page['#attached']['js'][] = array(
      'data' => drupal_get_path('module', 'exclude_node_title') . '/exclude_node_title.js',
      'type' => 'file',
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $config = \Drupal::config('exclude_node_title.settings');

    foreach ($form_state['values'] as $key => $value) {
      if (drupal_substr($key, 0, 18) == 'exclude_node_title') {
        $config->set($key, $value);
      }
    }

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
