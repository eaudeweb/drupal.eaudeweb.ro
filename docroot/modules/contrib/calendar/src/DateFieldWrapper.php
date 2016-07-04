<?php
/**
 * @file
 * Contains \Drupal\calendar\DateFieldWrapper.
 */


namespace Drupal\calendar;

use Drupal\Core\Field\FieldItemList;

/**
 * Defines wrapper class for a date field.
 */
class DateFieldWrapper {

  /**
   * @var \Drupal\Core\Field\FieldItemList
   */
  protected $fieldItemList;

  /**
   * DateFieldWrapper constructor.
   */
  public function __construct(FieldItemList $fieldItemList) {
    $this->fieldItemList = $fieldItemList;
  }

  /**
   * Getter for the start date.
   *
   * @param int $index
   *   The index of the field value to be retrieved. Defaults to 0.
   *
   * @return \DateTime
   */
  public function getStartDate($index = 0) {
    $date = new \DateTime();
    return $date->setTimestamp($this->getStartTimeStamp($index));
  }

  /**
   * Getter for the end date.
   *
   * @param int $index
   *   The index of the field value to be retrieved. Defaults to 0.
   *
   * @TODO Implement support for end date timestamps when it comes available
   * through core/contrib.
   *
   * @return \DateTime
   */
  public function getEndDate($index = 0) {
    $date = new \DateTime();
    return $date->setTimestamp($this->getEndTimeStamp($index));
  }

  /**
   * Getter for the start timestamp.
   *
   * @param int $index
   *   The index of the field value to be retrieved. Defaults to 0.
   *
   * @return int
   *   The start date as a UNIX timestamp.
   */
  protected function getStartTimeStamp($index = 0) {
    $value = $this->fieldItemList->getValue()[$index]['value'];
    $field_def = $this->fieldItemList->getFieldDefinition();
    $field_type = $field_def->getFieldStorageDefinition()->getType();
    if ($field_type == 'datetime') {
      /** @var \Drupal/datetime\Plugin\FieldType\DateTimeItem $field */
      $field = $this->fieldItemList->get($index);
      // Set User's Timezone
      $field->date->setTimezone(timezone_open(drupal_get_user_timezone()));
      // Format to timestamp.
      return $field->date->format('U');
    }
    return (int)$value;
  }

  /**
   * Getter for the end timestamp
   *
   * @param int $index
   *   The index of the field value to be retrieved. Defaults to 0.
   *
   * @TODO Implement support for end date timestamps when it comes available
   * through core/contrib.
   *
   * @return int
   *   The end date as a UNIX timestamp.
   */
  protected function getEndTimeStamp($index = 0) {
    return $this->getStartTimeStamp($index);
  }

  /**
   * @return bool
   */
  public function isEmpty() {
    return $this->fieldItemList->isEmpty();
  }
}
