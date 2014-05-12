<?php

namespace Drupal\exclude_node_title\Controller;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

class ExcludeNodeTitleController extends ControllerBase {

  public function adminSettings() {

    $form_builder = $this->formBuilder();
    $response = $form_builder->getForm('Drupal\exclude_node_title\Form\ExcludeNodeTitleAdminSettingsForm');

    return $response;
  }

}
