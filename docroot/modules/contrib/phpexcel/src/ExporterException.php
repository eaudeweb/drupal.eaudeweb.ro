<?php

/**
 * @file
 * Contains \Drupal\phpexcel\ExporterException.
 */

namespace Drupal\phpexcel;

/**
 * Exception class for \Drupal\phpexcel\Exporter.
 */
class ExporterException extends \Exception {

  const ERROR = 0;
  const ERROR_NO_HEADERS = 10;
  const ERROR_NO_DATA = 20;
  const ERROR_PATH_NOT_WRITABLE = 30;
  const ERROR_LIBRARY_NOT_FOUND = 40;
  const ERROR_FILE_NOT_WRITTEN = 50;
  const ERROR_TEMPLATE_NOT_READABLE = 60;
  const CACHING_METHOD_UNAVAILABLE = 70;

  function __construct($message = '', $code = 0) {
    parent::__construct($message, $code);
  }
}
