<?php

/**
 * @file
 * Contains \Drupal\yamlform\BreadCrumb\YamlFormBreadcrumbBuilder.
 */

namespace Drupal\yamlform\BreadCrumb;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\yamlform\YamlFormInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a YAML form breadcrumb builder.
 */
class YamlFormBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * The current route's entity or plugin type.
   *
   * @var string
   */
  protected $type;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $route_name = $route_match->getRouteName();
    // All routes must begin or contain 'yamlform.
    if (strpos($route_name, 'yamlform') === FALSE) {
      return FALSE;
    }

    $args = explode('.', $route_name);

    // Skip all config_translation routes except the overview
    // and allow Drupal to use the path as the breadcrumb.
    if (strpos($route_name, 'config_translation') !== FALSE && $route_name != 'entity.yamlform.config_translation_overview') {
      return FALSE;
    }

    if ($args[0] == 'entity' && ($args[2] == 'yamlform' ||  $args[2] == 'yamlform_submission')) {
      $this->type = 'yamlform_source_entity';
    }
    elseif (strpos($route_name, 'yamlform.handler_') === 0) {
      $this->type = 'yamlform_handler';
    }
    elseif (strpos($route_name, 'entity.yamlform_ui.element') === 0) {
      $this->type = 'yamlform_element';
    }
    elseif ($route_match->getParameter('yamlform_submission') instanceof YamlFormSubmissionInterface && $route_match->getParameter('yamlform_submission')->access('admin')) {
      $this->type = 'yamlform_submission';
    }
    elseif (($route_match->getParameter('yamlform') instanceof YamlFormInterface  && $route_match->getParameter('yamlform')->access('admin'))) {
      $this->type = 'yamlform';
    }
    else {
      $this->type = NULL;
    }

    return ($this->type) ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    if ($this->type == 'yamlform_source_entity') {
      /** @var \Drupal\yamlform\YamlFormRequestInterface $yamlform_request */
      $yamlform_request = \Drupal::service('yamlform.request');
      $source_entity = $yamlform_request->getCurrentSourceEntity(['yamlform', 'yamlform_submission']);
      $entity_type = $source_entity->getEntityTypeId();
      $entity_id = $source_entity->id();

      $breadcrumb = new Breadcrumb();
      $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));
      $breadcrumb->addLink($source_entity->toLink());
      if ($route_match->getParameter('yamlform_submission')) {
        $breadcrumb->addLink(Link::createFromRoute($this->t('Results'), "entity.$entity_type.yamlform.results_submissions", [$entity_type => $entity_id]));
      }
    }
    else {
      $breadcrumb = new Breadcrumb();
      $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));
      $breadcrumb->addLink(Link::createFromRoute($this->t('Administration'), 'system.admin'));
      $breadcrumb->addLink(Link::createFromRoute($this->t('Structure'), 'system.admin_structure'));
      $breadcrumb->addLink(Link::createFromRoute($this->t('YAML form'), 'entity.yamlform.collection'));
      switch ($this->type) {
        case 'yamlform_element':
          /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
          $yamlform = $route_match->getParameter('yamlform');
          $breadcrumb->addLink(Link::createFromRoute($yamlform->label(), 'entity.yamlform.canonical', ['yamlform' => $yamlform->id()]));
          $breadcrumb->addLink(Link::createFromRoute('Elements', 'entity.yamlform.edit_form', ['yamlform' => $yamlform->id()]));
          break;

        case 'yamlform_handler':
          if ($route_match->getRouteName() != 'yamlform.handler_plugins') {
            /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
            $yamlform = $route_match->getParameter('yamlform');
            $breadcrumb->addLink(Link::createFromRoute($yamlform->label(), 'entity.yamlform.canonical', ['yamlform' => $yamlform->id()]));
            $breadcrumb->addLink(Link::createFromRoute('Emails / Handlers', 'entity.yamlform.handlers_form', ['yamlform' => $yamlform->id()]));
          }
          break;

        case 'yamlform_options':
          if ($route_match->getRouteName() != 'entity.yamlform_options.collection') {
            $breadcrumb->addLink(Link::createFromRoute($this->t('Options'), 'entity.yamlform_options.collection'));
          }
          break;

        case 'yamlform_submission':
          /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
          $yamlform_submission = $route_match->getParameter('yamlform_submission');
          $yamlform = $yamlform_submission->getYamlForm();

          $breadcrumb->addLink(Link::createFromRoute($yamlform->label(), 'entity.yamlform.canonical', ['yamlform' => $yamlform->id()]));
          $breadcrumb->addLink(Link::createFromRoute($this->t('Results'), 'entity.yamlform.results_submissions', ['yamlform' => $yamlform->id()]));
          break;
      }
    }

    // This breadcrumb builder is based on a route parameter, and hence it
    // depends on the 'route' cache context.
    $breadcrumb->addCacheContexts(['route']);

    return $breadcrumb;
  }

}
