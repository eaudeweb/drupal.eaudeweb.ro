<?php

/**
 * @file
 * Contains \Drupal\yamlform\Entity\YamlFormSubmissionListBuilder.
 */

namespace Drupal\yamlform;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a list controller for yamlform submission entity.
 *
 * @ingroup yamlform
 */
class YamlFormSubmissionListBuilder extends EntityListBuilder {

  /**
   * The YAML form.
   *
   * @var \Drupal\yamlform\YamlFormInterface
   */
  protected $yamlform;

  /**
   * The entity that a YAML form is attached to. Currently only applies to nodes.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $sourceEntity;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The table header columns.
   *
   * @var array
   */
  protected $header;

  /**
   * The YAML form elements.
   *
   * @var array
   */
  protected $elements = [];

  /**
   * The YAML form results filter search keys.
   *
   * @var string
   */
  protected $keys;

  /**
   * The YAMl form element manager.
   *
   * @var \Drupal\yamlform\YamlFormElementManagerInterface
   */
  protected $elementManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage) {
    parent::__construct($entity_type, $storage);

    /** @var \Drupal\yamlform\YamlFormRequestInterface $yamlform_request */
    $yamlform_request = \Drupal::service('yamlform.request');

    $this->keys = \Drupal::request()->query->get('search');
    list($this->yamlform, $this->sourceEntity) = $yamlform_request->getYamlFormEntities();
    $base_route_name = $this->getBaseRouteName();

    $this->account = (\Drupal::routeMatch()->getRouteName() == "$base_route_name.yamlform.submissions") ? \Drupal::currentUser() : FALSE;

    if (\Drupal::routeMatch()->getRouteName() == "$base_route_name.yamlform.results_table") {
      $this->elements = $this->yamlform->getElementsFlattenedAndHasValue();
      // Use the default format when displaying each element.
      foreach ($this->elements as &$element) {
        unset($element['#format']);
      }
    }
    $this->elementManager = \Drupal::service('plugin.manager.yamlform.element');
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    if ($this->yamlform) {
      if ($this->account) {
        $build['#title'] = $this->t('Submissions to %yamlform for %user', [
          '%yamlform' => $this->yamlform->label(),
          '%user' => $this->account->getDisplayName(),
        ]);
      }
    }

    // Add the filter.
    $build['filter_form'] = \Drupal::formBuilder()->getForm('\Drupal\yamlform\Form\YamlFormFilterForm', $this->t('submissions'), $this->t('Filter by submitted data'), $this->keys);

    // Display info.
    if ($total = $this->getTotal()) {
      $t_args = [
        '@total' => $total,
        '@results' => $this->formatPlural($total, $this->t('submission'), $this->t('submissions')),
      ];
      $build['info'] = [
        '#markup' => $this->t('@total @results', $t_args),
        '#prefix' => '<div>',
        '#suffix' => '</div>',
      ];
    }

    $build += parent::render();

    $build['table']['#attributes']['class'][] = 'yamlform-results';

    $build['#attached']['library'][] = 'yamlform/yamlform';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    if (isset($this->header)) {
      return $this->header;
    }

    $view_any = ($this->yamlform && $this->yamlform->access('submission_view_any')) ? TRUE : FALSE;

    $header['sid'] = [
      'data' => $this->t('#'),
      'field' => 'sid',
      'specifier' => 'sid',
      'sort' => 'desc',
    ];

    $header['created'] = [
      'data' => $this->t('Submitted'),
      'field' => 'created',
      'specifier' => 'created',
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];

    if ($view_any && empty($this->sourceEntity)) {
      $header['entity'] = [
        'data' => $this->t('Submitted to'),
      ];
    }

    if (empty($this->account)) {
      $header['uid'] = [
        'data' => $this->t('User'),
        'field' => 'uid',
        'specifier' => 'uid',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ];
    }

    if ($view_any && $this->moduleHandler()->moduleExists('language')) {
      $header['langcode'] = [
        'data' => $this->t('Language'),
        'field' => 'langcode',
        'specifier' => 'langcode',
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ];
    }

    $header['remote_addr'] = [
      'data' => $this->t('IP address'),
      'field' => 'remote_addr',
      'specifier' => 'remote_addr',
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];

    if (empty($this->yamlform) && empty($this->sourceEntity)) {
      $header['yamlform'] = [
        'data' => $this->t('Form'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ];
    }

    if ($this->elements) {
      foreach ($this->elements as $key => $element) {
        $header['element_' . $key] = $element['#title'] ?: $key;
      }
    }

    // Cache header in protected variable.
    $this->header = $header + parent::buildHeader();
    return $this->header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $route_name = $this->getBaseRouteName() . '.yamlform_submission.canonical';
    $route_parameters = $this->getRouteParameters($entity);
    $link_text = $entity->id() . ($entity->isDraft() ? ' (' . $this->t('draft') . ')' : '');

    /* @var $entity \Drupal\yamlform\YamlFormSubmissionInterface */
    $view_any = ($this->yamlform && $this->yamlform->access('submission_view_any')) ? TRUE : FALSE;

    $row['sid'] = Link::createFromRoute($link_text, $route_name, $route_parameters);

    $row['created'] = \Drupal::service('date.formatter')->format($entity->created->value);

    if ($view_any && !$this->sourceEntity) {
      $row['entity'] = ($source_entity = $entity->getSourceEntity()) ? $source_entity->toLink() : '';
    }

    if (empty($this->account)) {
      $row['user'] = $entity->getOwner()->getAccountName() ?: t('Anonymous');
    }

    if ($view_any && $this->moduleHandler()->moduleExists('language')) {
      $row['langcode'] = \Drupal::languageManager()->getLanguage($entity->langcode->value)->getName();
    }

    $row['remote_addr'] = $entity->getRemoteAddr();

    if (empty($this->yamlform) && empty($this->sourceEntity)) {
      $row['yamlform'] = $entity->getYamlForm()->toLink();
    }

    if ($this->elements) {
      $data = $entity->getData();
      foreach ($this->elements as $key => $element) {
        $options = [];
        $html = $this->elementManager->invokeMethod('formatHtml', $element, $data[$key], $options);
        if (is_array($html)) {
          $row['element_' . $key] = ['data' => $html];
        }
        else {
          $row['element_' . $key] = $html;
        }
      }
    }

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $base_route_name = $this->getBaseRouteName();
    $route_parameters = $this->getRouteParameters($entity);
    $route_options = ['query' => \Drupal::destination()->getAsArray()];

    $operations = [];

    if ($entity->access('update')) {
      $operations['edit'] = [
        'title' => $this->t('Edit'),
        'weight' => 10,
        'url' => Url::fromRoute("$base_route_name.yamlform_submission.edit_form", $route_parameters, $route_options),
      ];
    }

    if ($entity->access('view')) {
      $operations['view'] = [
        'title' => $this->t('View'),
        'weight' => 20,
        'url' => Url::fromRoute("$base_route_name.yamlform_submission.canonical", $route_parameters),
      ];
    }

    if ($entity->access('update')) {
      $operations['resend'] = [
        'title' => $this->t('Resend'),
        'weight' => 21,
        'url' => Url::fromRoute("$base_route_name.yamlform_submission.resend_form", $route_parameters, $route_options),
      ];
    }

    if ($entity->access('delete')) {
      $operations['delete'] = [
        'title' => $this->t('Delete'),
        'weight' => 100,
        'url' => Url::fromRoute("$base_route_name.yamlform_submission.delete_form", $route_parameters, $route_options),
      ];
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $header = $this->buildHeader();
    return $this->getQuery()
      ->pager($this->limit)
      ->tableSort($header)
      ->execute();
  }

  /**
   * Get base route name for the YAML form or YAML form source entity.
   *
   * @return string
   *   The base route name for YAML form or YAML form source entity.
   */
  protected function getBaseRouteName() {
    if ($this->sourceEntity) {
      return 'entity.' . $this->sourceEntity->getEntityTypeId();
    }
    else {
      return 'entity';
    }
  }

  /**
   * Get route parameters for the YAML form or YAML form source entity.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   *
   * @return array
   *   Route parameters for the YAML form or YAML form source entity.
   */
  protected function getRouteParameters(YamlFormSubmissionInterface $yamlform_submission) {
    $router_parameters = ['yamlform_submission' => $yamlform_submission->id()];
    if ($this->sourceEntity) {
      $router_parameters[$this->sourceEntity->getEntityTypeId()] = $this->sourceEntity->id();
    }
    return $router_parameters;
  }

  /**
   * Get the total number of submissions.
   *
   * @return int
   *   The total number of submissions.
   */
  protected function getTotal() {
    return $this->getQuery()
      ->count()
      ->execute();
  }

  /**
   * Get the base entity query filtered by YAML form and search.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   An entity query.
   */
  protected function getQuery() {
    $query = $this->getStorage()->getQuery();

    // Limit submission to the current YAML form.
    if ($this->yamlform) {
      $query->condition('yamlform_id', $this->yamlform->id());
    }

    // Limit submission to the current user.
    if ($this->account) {
      $query->condition('uid', $this->account->id());
    }

    // Filter entity type and id. (Currently only applies to yamlform_node.module)
    if ($this->sourceEntity) {
      $query->condition('entity_type', $this->sourceEntity->getEntityTypeId());
      $query->condition('entity_id', $this->sourceEntity->id());
    }

    // Filter submissions.
    if ($this->keys) {
      $sub_query = db_select('yamlform_submission_data', 'sd')
        ->fields('sd', ['sid'])
        ->condition('yamlform_id', $this->yamlform->id())
        ->condition('value', '%' . $this->keys . '%', 'LIKE');
      $query->condition('sid', $sub_query, 'IN');
    }

    return $query;
  }

}
