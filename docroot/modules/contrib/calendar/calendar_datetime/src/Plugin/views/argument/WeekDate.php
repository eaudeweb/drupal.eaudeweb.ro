<?php

/**
 * @file
 * Contains \Drupal\calendar_datetime\Plugin\views\argument\WeekDate.
 */

namespace Drupal\calendar_datetime\Plugin\views\argument;

use Drupal\datetime\Plugin\views\argument\Date;

/**
 * Argument handler for a day.
 *
 * @ViewsArgument("datetime_week")
 */
class WeekDate extends Date {

  /**
   * {@inheritdoc}
   */
  protected $argFormat = 'W';

}
