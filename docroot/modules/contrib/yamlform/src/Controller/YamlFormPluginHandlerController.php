<?php

/**
 * @file
 * Contains \Drupal\yamlform\Controller\YamlFormPluginHandlerController.
 */

namespace Drupal\yamlform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Controller for all YAML form handlers.
 */
class YamlFormPluginHandlerController extends ControllerBase {

  /**
   * A YAML form handler plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

  /**
   * Constructs a YamlFormPluginBaseController object.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   A YAML form handler plugin manager.
   */
  public function __construct(PluginManagerInterface $plugin_manager) {
    $this->pluginManager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.yamlform.handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function index() {
    $plugin_definitions = $this->pluginManager->getDefinitions();

    $rows = [];
    foreach ($plugin_definitions as $plugin_id => $plugin_definition) {
      $rows[$plugin_id] = [
        $plugin_id,
        $plugin_definition['label'],
        $plugin_definition['description'],
        ($plugin_definition['cardinality'] == -1) ? $this->t('Unlimited') : $plugin_definition['cardinality'],
        $plugin_definition['results'] ? $this->t('Processed') : $this->t('Ignored'),
        $plugin_definition['provider'],
      ];
    }

    ksort($rows);
    return [
      '#type' => 'table',
      '#header' => [
        $this->t('ID'),
        $this->t('Label'),
        $this->t('Description'),
        $this->t('Cardinality'),
        $this->t('Results'),
        $this->t('Provided by'),
      ],
      '#rows' => $rows,
      '#sticky' => TRUE,
    ];
  }

}
