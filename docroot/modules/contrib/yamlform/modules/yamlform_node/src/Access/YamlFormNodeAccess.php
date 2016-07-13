<?php

/**
 * @file
 * Definition of Drupal\yamlform_node\Access\YamlFormNodeAccess.
 */

namespace Drupal\yamlform_node\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Defines the custom access control handler for the YAML form node.
 */
class YamlFormNodeAccess {

  /**
   * Check whether the user can access a node's YAML form results.
   *
   * @param string $operation
   *   Operation being performed.
   * @param \Drupal\node\NodeInterface $node
   *   A node.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  static public function checkYamlFormAccess($operation = '', NodeInterface $node, AccountInterface $account) {
    if (!$node->hasField('yamlform') || !$node->yamlform->entity) {
      return AccessResult::forbidden();
    }
    elseif (strpos($operation, 'yamlform.') === 0) {
      return $node->yamlform->entity->access($operation, $account, TRUE);
    }
    else {
      return $node->access($operation, $account, TRUE);
    }
  }

  /**
   * Check whether the user can access a node's YAML form submission.
   *
   * @param string $operation
   *   Operation being performed.
   * @param \Drupal\node\NodeInterface $node
   *   A node.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  static public function checkYamlFormSubmissionAccess($operation = '', NodeInterface $node, YamlFormSubmissionInterface $yamlform_submission, AccountInterface $account) {
    if (!$node->hasField('yamlform') || !$node->yamlform->entity) {
      return AccessResult::forbidden();
    }
    elseif ($yamlform_submission->getSourceEntity() != $node) {
      return AccessResult::forbidden();
    }
    else {
      return $node->access($operation, $account, TRUE);
    }
  }

}
