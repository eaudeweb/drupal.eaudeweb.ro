<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormSubmissionStorage.
 */

namespace Drupal\yamlform;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the YAML form submission storage.
 */
class YamlFormSubmissionStorage extends SqlContentEntityStorage implements YamlFormSubmissionStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinitions() {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $definitions */
    $field_definitions = $this->entityManager->getBaseFieldDefinitions('yamlform_submission');

    // For now never let any see or export the serialize YAML data field.
    unset($field_definitions['data']);

    $definitions = [];
    foreach ($field_definitions as $field_name => $field_definition) {
      $definitions[$field_name] = [
        'title' => $field_definition->getLabel(),
        'name' => $field_name,
        'type' => $field_definition->getType(),
        'target_type' => $field_definition->getSetting('target_type'),
      ];
    }

    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function loadDraft(YamlFormInterface $yamlform, EntityInterface $source_entity = NULL, AccountInterface $account) {
    $query = $this->getQuery();
    $query->condition('in_draft', 1);
    $query->condition('yamlform_id', $yamlform->id());
    $query->condition('uid', $account->id());
    if ($source_entity) {
      $query->condition('entity_type', $source_entity->getEntityTypeId());
      $query->condition('entity_id', $source_entity->id());
    }
    else {
      $query->condition('entity_type', NULL, 'IS');
      $query->condition('entity_id', NULL, 'IS');
    }
    if ($entity_ids = $query->execute()) {
      return $this->load(reset($entity_ids));
    }
    else {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function hasPrevious(YamlFormInterface $yamlform, EntityInterface $source_entity = NULL, AccountInterface $account) {
    $query = $this->getQuery();
    $query->condition('in_draft', 0);
    $query->condition('yamlform_id', $yamlform->id());
    $query->condition('uid', $account->id());
    if ($source_entity) {
      $query->condition('entity_type', $source_entity->getEntityTypeId());
      $query->condition('entity_id', $source_entity->id());
    }
    else {
      $query->condition('entity_type', NULL, 'IS');
      $query->condition('entity_id', NULL, 'IS');
    }
    return ($query->execute()) ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function doCreate(array $values) {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $entity */
    $entity = parent::doCreate($values);
    if (!empty($values['data'])) {
      $data = (is_array($values['data'])) ? $values['data'] : Yaml::decode($values['data']);
      $entity->setData($data);
    }
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $ids = NULL) {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface[] $yamlform_submissions */
    $yamlform_submissions = parent::loadMultiple($ids);

    // Load YAML form submission data.
    if ($sids = array_keys($yamlform_submissions)) {
      $result = db_select('yamlform_submission_data', 'sd')
        ->fields('sd', ['sid', 'name', 'delta', 'value'])
        ->condition('sd.sid', $sids, 'IN')
        ->orderBy('sd.sid', 'ASC')
        ->orderBy('sd.name', 'ASC')
        ->orderBy('sd.delta', 'ASC')
        ->execute();
      $submissions_data = [];
      while ($record = $result->fetchAssoc()) {
        if ($record['delta'] === '') {
          $submissions_data[$record['sid']][$record['name']] = $record['value'];
        }
        else {
          $submissions_data[$record['sid']][$record['name']][$record['delta']] = $record['value'];
        }
      }

      // Set YAML form submission data via setData().
      foreach ($submissions_data as $sid => $submission_data) {
        $yamlform_submissions[$sid]->setData($submission_data);
        $yamlform_submissions[$sid]->setOriginalData($submission_data);
      }
    }

    return $yamlform_submissions;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll(YamlFormInterface $yamlform = NULL, EntityInterface $source_entity = NULL, $limit = NULL, $max_sid = NULL) {
    $query = $this->getQuery()
      ->sort('sid');
    if ($yamlform) {
      $query->condition('yamlform_id', $yamlform->id());
    }
    if ($source_entity) {
      $query->condition('entity_type', $source_entity->getEntityTypeId());
      $query->condition('entity_id', $source_entity->id());
    }
    if ($limit) {
      $query->range(0, $limit);
    }
    if ($max_sid) {
      $query->condition('sid', $max_sid, '<=');
    }

    $entity_ids = $query->execute();
    $entities = $this->loadMultiple($entity_ids);
    $this->delete($entities);
    return count($entities);
  }

  /**
   * {@inheritdoc}
   */
  public function getTotal(YamlFormInterface $yamlform = NULL, EntityInterface $source_entity = NULL, AccountInterface $account = NULL) {
    $query = $this->getQuery()->count();
    if ($yamlform) {
      $query->condition('yamlform_id', $yamlform->id());
    }
    if ($source_entity) {
      $query->condition('entity_type', $source_entity->getEntityTypeId());
      $query->condition('entity_id', $source_entity->id());
    }
    if ($account) {
      $query->condition('uid', $account->id());
    }
    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getMaxSubmissionId(YamlFormInterface $yamlform = NULL, EntityInterface $source_entity = NULL, AccountInterface $account = NULL) {
    $query = $this->getQuery();
    $query->sort('sid', 'DESC');
    if ($yamlform) {
      $query->condition('yamlform_id', $yamlform->id());
    }
    if ($source_entity) {
      $query->condition('entity_type', $source_entity->getEntityTypeId());
      $query->condition('entity_id', $source_entity->id());
    }
    if ($account) {
      $query->condition('uid', $account->id());
    }
    $query->range(0, 1);
    $result = $query->execute();
    return reset($result);
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviousSubmission(YamlFormSubmissionInterface $yamlform_submission, EntityInterface $source_entity = NULL, AccountInterface $account) {
    return $this->getSiblingSubmission($yamlform_submission, $source_entity, $account, 'previous');
  }

  /**
   * {@inheritdoc}
   */
  public function getNextSubmission(YamlFormSubmissionInterface $yamlform_submission, EntityInterface $source_entity = NULL, AccountInterface $account) {
    return $this->getSiblingSubmission($yamlform_submission, $source_entity, $account, 'next');
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceEntityTypes(YamlFormInterface $yamlform) {
    $entity_types = db_select('yamlform_submission', 's')
      ->distinct()
      ->fields('s', ['entity_type'])
      ->condition('s.yamlform_id', $yamlform->id())
      ->condition('s.entity_type', 'yamlform', '<>')
      ->orderBy('s.entity_type', 'ASC')
      ->execute()
      ->fetchCol();

    $entity_type_labels = \Drupal::entityManager()->getEntityTypeLabels();
    ksort($entity_type_labels);

    return array_intersect_key($entity_type_labels, array_flip($entity_types));
  }

  /**
   * Get a YAML form submission's previous or next sibling.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   * @param \Drupal\Core\Entity\EntityInterface|NULL $entity
   *   (optional) A YAML form submission source entity.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user account.
   * @param string $direction
   *   The direction, previous or next, of the sibling to return.
   *
   * @return \Drupal\yamlform\YamlFormSubmissionInterface|null
   *   The YAML form submission's previous or next sibling.
   */
  protected function getSiblingSubmission(YamlFormSubmissionInterface $yamlform_submission, EntityInterface $entity = NULL, AccountInterface $account = NULL, $direction = 'previous') {
    $yamlform = $yamlform_submission->getYamlForm();

    $query = $this->getQuery();
    $query->condition('yamlform_id', $yamlform->id());
    $query->range(0, 1);

    if ($entity) {
      $query->condition('entity_type', $entity->getEntityTypeId());
      $query->condition('entity_id', $entity->id());
    }

    if ($account) {
      $access_any = $yamlform->access('view_any', $account);
      $entity_access_any = ($entity && $entity->access('yamlform_submission_view'));
      if (!$access_any && !$entity_access_any) {
        $query->condition('uid', $account->id());
      }
    }

    if ($direction == 'previous') {
      $query->condition('sid', $yamlform_submission->id(), '<');
      $query->sort('sid', 'DESC');
    }
    else {
      $query->condition('sid', $yamlform_submission->id(), '>');
      $query->sort('sid', 'ASC');
    }

    return ($entity_ids = $query->execute()) ? $this->load(reset($entity_ids)) : NULL;
  }

}
