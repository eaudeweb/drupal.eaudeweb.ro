<?php

/**
 * @file
 * Contains \Drupal\phpexcel\Exporter.
 */

namespace Drupal\phpexcel;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\phpexcel\ExporterException;
use PHPExcel;
use PHPExcel_Settings;
use PHPExcel_IOFactory;
use PHPExcel_CachedObjectStorageFactory;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Writer_CSV;
use PHPExcel_Writer_OpenDocument;
use PHPExcel_Writer_Excel5;
use PHPExcel_DocumentProperties;

/**
 * Exporter code to PHPExcel library.
 *
 * Exporter class to provide an easy-to-use, Drupal-friendly interface for
 * exporting or importing data with PHPExcel.
 */
class Exporter {
  use StringTranslationTrait;

  /**
   * Configuration object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory->get('phpexcel.settings');
  }

  /**
   * Export data to an Excel file.
   *
   * @throws Drupal\phpexcel\ExporterException
   *    In case of an error, throws an exception with information about what
   *    went wrong.
   *
   * @param array $headers
   *    An array containing all headers. Example:
   *    @code
   *    $headers = array(
   *      'Header 1',
   *      'Header 2',
   *      'Header 3',
   *    );
   *    @endcode
   *    If given a two-dimensional array, each item in the first level
   *    defines a new worksheet, with the key being the worksheet name. All
   *    second level entries will be treated as the worksheet headers.
   *    @code
   *    $headers = array(
   *      'Worksheet 1' => array(
   *        'Header 1',
   *        'Header 2',
   *        'Header 3',
   *      ),
   *      'Worksheet 2' => array(
   *        'Header 1',
   *        'Header 2',
   *        'Header 3',
   *      ),
   *    );
   *    @endcode
   * @param array $data
   *     A two-dimensional array containing all data, grouped by row.
   *     @code
   *     $data = array(
   *       array(
   *         'Cell A:1',
   *         'Cell A:2',
   *         'Cell A:3',
   *       ),
   *       array(
   *         'Cell B:1',
   *         'Cell B:2',
   *         'Cell B:3',
   *       ),
   *     );
   *     @endcode
   *     If the $headers also contain worksheet information, the data must be a
   *     three-dimensional array, with the first dimenstion being the worksheet.
   *     @code
   *     $data = array(
   *       array(
   *         // Worksheet 1.
   *         array(
   *           'Cell A:1',
   *           'Cell A:2',
   *           'Cell A:3',
   *         ),
   *         array(
   *           'Cell B:1',
   *           'Cell B:2',
   *           'Cell B:3',
   *         ),
   *       ),
   *       // Worksheet 2.
   *       array(
   *         array(
   *           'Cell A:1',
   *           'Cell A:2',
   *           'Cell A:3',
   *         ),
   *         array(
   *           'Cell B:1',
   *           'Cell B:2',
   *           'Cell B:3',
   *         ),
   *       ),
   *     );
   *     @endcode
   * @param string $path
   *    The path where the file will be saved. Must be writable.
   * @param array $options
   *    (optional) An array of options. Possible options are:
   *    - format: The file format. Can either be "xls", "xlsx", "ods", or "csv".
   *      Defaults to "xls".
   *    - template: The path to an existing template file. Defaults to NULL.
   *    - creator: Author metadata (called "creator" in PHPExcel). Defaults to
   *      an empty string.
   *    - title: Title metadata. Defaults to an empty string.
   *    - subject: Subject metadata. Defaults to an empty string.
   *    - description: Description metadata. Defaults to an empty string.
   *    The options array is passed as-is to modules implementing the export
   *    hooks. Calling modules may add any other metadata or export information
   *    to the options array, as needed.
   *
   * @return bool
   *    TRUE on success, FALSE otherwise.
   */
  public function export(array $headers, array $data, $path, array $options = array()) {
    if (empty($headers) && empty($options['ignore_headers'])) {
      throw new ExporterException($this->t("No headers provided, yet the ignore_headers option is FALSE."), ExporterException::ERROR_NO_HEADERS);
    }

    if (empty($data)) {
      throw new ExporterException($this->t("No data provided."), ExporterException::ERROR_NO_DATA);
    }

    if (!(is_writable($path) || (!file_exists($path) && is_writable(dirname($path))))) {
      throw new ExporterException($this->t("Path @path is not writable.", array('@path' => $path)), ExporterException::ERROR_PATH_NOT_WRITABLE);
    }

    $library = libraries_load('PHPExcel');

    if (empty($library['loaded'])) {
      throw new ExporterException($this->t("Library PHPExcel could not be loaded."), ExporterException::ERROR_LIBRARY_NOT_FOUND);
    }

    $path = $this->mungeFilename($path);

    // Determine caching method.
    $cacheSettings = array();
    switch ($this->config->get('cache_mechanism')) {
      case 'cache_in_memory_serialized':
        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
        break;

      case 'cache_in_memory_gzip':
        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        break;

      case 'cache_to_phpTemp':
        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = array(
          'memoryCacheSize' => $this->config->get('phptemp_limit') . 'MB'
        );
        break;

      case 'cache_to_apc':
        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_apc;
        $cacheSettings = array(
          'cacheTime' => $this->config->get('apc_cachetime')
        );
        break;

      case 'cache_to_memcache':
        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_memcache;
        $cacheSettings = array(
          'memcacheServer' => $this->config->get('memcache_host'),
          'memcachePort' => $this->config->get('memcache_port'),
          'cacheTime' => $this->config->get('memcache_cachetime')
        );
        break;

      case 'cache_to_sqlite3':
        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_sqlite3;
        break;

      default:
        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory;
        break;
    }

    // Failsafe.
    if (empty($cacheMethod)) {
      throw new ExporterException($this->t("Caching method is not supported."), ExporterException::CACHING_METHOD_UNAVAILABLE);
    }

    PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

    // Must we render from a template file ?
    if (!empty($options['template'])) {
      if (!is_readable($options['template'])) {
        throw new ExporterException($this->t("Template @path is not readable.", array('@path' => $options['template'])), ExporterException::ERROR_TEMPLATE_NOT_READABLE);
      }

      $xlsReader = PHPExcel_IOFactory::createReaderForFile($options['template']);
      $xls = $xlsReader->load($options['template']);
    }
    else {
      $xls = new PHPExcel();
    }

    if (!empty($options)) {
      $this->setProperties($xls->getProperties(), $options);
    }

    // Must we ignore the headers ?
    if (empty($options['ignore_headers'])) {
      $this->setHeaders($xls, $headers, $options);
    }

    // _phpexcel_set_columns($xls, $data, empty($options['ignore_headers']) ? $headers : NULL, $options);

    $format = isset($options['format']) ? strtolower($options['format']) : 'xls';

    switch($format) {
      case 'xlsx':
        $writer = new PHPExcel_Writer_Excel2007($xls);
        break;
      case 'csv':
        $writer = new PHPExcel_Writer_CSV($xls);
        break;
      case 'ods':
        $writer = new PHPExcel_Writer_OpenDocument($xls);
        break;
      default:
        $writer = new PHPExcel_Writer_Excel5($xls);
    }

    $writer->save($path);

    return file_exists($path);
  }

  /**
   * Sets the Excel file properties, like creator, title, etc.
   *
   * @param PHPExcel_DocumentProperties $properties
   *    The properties object of the Excel document.
   * @param array $options
   *    The options array passed to Drupal\phpexcel\Exporter::export().
   */
  protected function setProperties(PHPExcel_DocumentProperties $properties, array $options) {
    if (isset($options['creator'])) {
      $properties->setCreator($options['creator']);
    }
    else {
      $properties->setCreator("PHPExcel");
    }

    if (isset($options['title'])) {
      $properties->setTitle($options['title']);
    }

    if (isset($options['subject'])) {
      $properties->setSubject($options['subject']);
    }

    if (isset($options['description'])) {
      $properties->setDescription($options['description']);
    }
  }

  /**
   * Sets the Excel file headers.
   *
   * @param PHPExcel $xls
   *    The PHPExcel object, which will be used for the export.
   * @param array $headers
   *    The headers for this file. See Drupal\phpexcel\Exporter::export() for
   *    more information.
   * @param array $options
   *    An array of options. See Drupal\phpexcel\Exporter::export() for more
   *    information.
   */
  protected function setHeaders(PHPExcel $xls, array $headers, array $options) {
    // Prior to PHP 5.3, calling current() on an associative array would not
    // work. Get only array values, just in case.
    if (!is_array(current(array_values($headers)))) {
      $headers = array($headers);
    }

    $this->invoke('export', 'headers', $headers, $xls, $options);

    $sheetId = 0;
    foreach ($headers as $sheetName => $sheetHeaders) {
      if ($sheetId) {
        $xls->createSheet($sheetId);
        $sheet = $xls->setActiveSheetIndex($sheetId);
      }
      else {
        // PHPExcel always creates one sheet.
        $sheet = $xls->getSheet();
      }

      if (!is_numeric($sheetName)) {
        $sheet->setTitle($sheetName);
      }
      else {
        $sheet->setTitle($this->t("Worksheet !id", array('!id' => ($sheetId + 1))));
      }

      $this->invoke('export', 'new sheet', $sheetId, $xls, $options);

      for ($i = 0, $len = count($sheetHeaders); $i < $len; $i++) {
        $value = trim($sheetHeaders[$i]);

        $this->invoke('export', 'pre cell', $value, $sheet, $options, $i, 1);

        $sheet->setCellValueByColumnAndRow($i, 1, $value);

        $this->invoke('export', 'post cell', $value, $sheet, $options, $i, 1);
      }

      $sheetId++;
    }
  }

  /**
   * Invokes phpexcel hooks
   *
   * We need a custom hook-invoke method, because we need to pass parameters by
   * reference.
   */
  protected function invoke($hook, $op, &$data, $phpexcel, $options, $column = NULL, $row = NULL) {
    foreach (module_implements('phpexcel_' . $hook) as $module) {
      $function = $module . '_phpexcel_' . $hook;

      $function($op, $data, $phpexcel, $options, $column, $row);
    }
  }

  /**
   * Munges the filename in the path.
   *
   * We can't use Core's file_munge_filename() directly because the $path
   * variable contains the path as well. Separate the filename from the
   * directory structure, munge it and return.
   *
   * @see file_munge_filename()
   *
   * @param string $path
   *    The path where the data will get written to.
   *
   * @return string
   *    The path, munged and safe.
   */
  protected function mungeFilename($path) {
    $parts = explode('/', $path);

    $filename = array_pop($parts);

    return implode('/', $parts) . '/' . file_munge_filename($filename, 'xls xlsx csv xml');
  }
}
