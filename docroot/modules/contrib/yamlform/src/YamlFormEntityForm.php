<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormEntityForm.
 */

namespace Drupal\yamlform;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base for controller for YAML form.
 */
class YamlFormEntityForm extends BundleEntityFormBase {

  /**
   * YAML form element manager.
   *
   * @var \Drupal\yamlform\YamlFormElementManagerInterface
   */
  protected $elementManager;

  /**
   * YAML form element validator.
   *
   * @var \Drupal\yamlform\YamlFormEntityElementsValidator
   */
  protected $elementsValidator;

  /**
   * Constructs a new YamlFormUiElementFormBase.
   *
   * @param \Drupal\yamlform\YamlFormElementManagerInterface $element_manager
   *   The YAML form element manager.
   * @param \Drupal\yamlform\YamlFormEntityElementsValidator $elements_validator
   *   YAML form element validator.
   */
  public function __construct(YamlFormElementManagerInterface $element_manager, YamlFormEntityElementsValidator $elements_validator) {
    $this->elementManager = $element_manager;
    $this->elementsValidator = $elements_validator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.yamlform.element'),
      $container->get('yamlform.elements_validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareEntity() {
    if ($this->operation == 'duplicate') {
      $this->setEntity($this->getEntity()->createDuplicate());
    }

    parent::prepareEntity();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Customize title for duplicate form.
    if ($this->operation == 'duplicate') {
      $form['#title'] = $this->t('<em>Duplicate YAML form</em> @label', ['@label' => $this->entity->label()]);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $this->getEntity();

    // Only display id, title, and description for new forms.
    // Once a form is created this information is moved to the form's settings
    // tab.
    if ($yamlform->isNew()) {
      $form['id'] = [
        '#type' => 'machine_name',
        '#default_value' => $yamlform->id(),
        '#machine_name' => [
          'exists' => '\Drupal\yamlform\Entity\YamlForm::load',
          'source' => ['title'],
        ],
        '#maxlength' => 32,
        '#disabled' => (bool) $yamlform->id() && $this->operation != 'duplicate',
        '#required' => TRUE,
      ];

      $form['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Title'),
        '#maxlength' => 255,
        '#default_value' => $yamlform->label(),
        '#required' => TRUE,
        '#id' => 'title',
      ];
      $form['description'] = [
        '#type' => 'yamlform_codemirror',
        '#mode' => 'html',
        '#title' => $this->t('Administrative description'),
        '#default_value' => $yamlform->get('description'),
        '#rows' => 2,
      ];
      $form = $this->protectBundleIdElement($form);
    }

    // Display warning when editing a translated YAML form.
    if ($yamlform->hasTranslations()) {
      $t_args = [
        ':translation_href' => $yamlform->toUrl('config-translation-overview')->toString(),
        '%title' => $yamlform->label(),
      ];
      drupal_set_message($this->t('The %title form has <a href=":translation_href">translations</a> and its elements and properties can not be changed.', $t_args), 'warning');
    }

    // Call the isolated edit form for which can be overridden by the
    // yamlform_ui.module.
    $form = $this->editForm($form, $form_state);

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    unset($actions['delete']);
    return $actions;
  }

  /**
   * Edit YAML Form source code form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function editForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $this->getEntity();

    // Set default elements for new YAML form.
    if ($this->operation == 'default' && $yamlform->isNew() && empty($_POST)) {
      drupal_set_message(t('Below are some default elements to get you started. You can also duplicate existing <a href=":templates_href">templates</a>.', [':templates_href' => Url::fromRoute('entity.yamlform.collection', [], ['query' => ['search' => 'Template:']])->toString()]));
      if (!$yamlform->get('elements')) {
        $yamlform->set('elements', $this->configFactory()->get('yamlform.settings')->get('elements.default_elements'));
      }
    }

    $t_args = [
      ':form_api_href' => 'https://www.drupal.org/node/37775',
      ':render_api_href' => 'https://www.drupal.org/developing/api/8/render',
      ':yaml_href' => 'https://en.wikipedia.org/wiki/YAML',
    ];
    $form['elements'] = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'yaml',
      '#title' => $this->t('Elements (YAML)'),
      '#description' => $this->t('Enter a <a href=":form_api_href">Form API (FAPI)</a> and/or a <a href=":render_api_href">Render Array</a> as <a href=":yaml_href">YAML</a>.', $t_args),
      '#default_value' => $yamlform->get('elements') ,
      '#required' => TRUE,
    ];

    $form['token_tree_link'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => [
        'yamlform',
      ],
      '#click_insert' => FALSE,
      '#dialog' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Validate elements YAML.
    if ($messages = $this->elementsValidator->validate($this->getEntity())) {
      $form_state->setErrorByName('elements');
      foreach ($messages as $message) {
        drupal_set_message($message, 'error');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $this->getEntity();
    $yamlform->save();

    $this->logger('yamlform')->notice('YAML form @label elements saved.', ['@label' => $yamlform->label()]);
    drupal_set_message($this->t('YAML form %label elements saved.', ['%label' => $yamlform->label()]));
    $form_state->setRedirect('entity.yamlform.edit_form', ['yamlform' => $yamlform->id()]);
  }

}
