<?php

namespace Drupal\tbo_general\Plugin\Config\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tbo_general\Plugin\Block\LauncherBlock;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Manage config a 'LauncherBlock' block.
 */
class LauncherBlockClass {

  protected $instance;

  protected $configuration;

  /**
   * @param \Drupal\tbo_general\Plugin\Block\LauncherBlock $instance
   * @param $config
   */
  public function setConfig(LauncherBlock &$instance, &$config) {
    $this->instance = &$instance;
    $this->configuration = &$config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $categories = \Drupal::service('tbo_general.launcher')->optionsSelectCategory();
    return ['options' => $categories];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm() {
    $form = $this->instance->cardBlockForm();
    $form['category'] = [
      '#type' => 'select',
      '#title' => t(' Asociar a una categoría'),
      '#options' => $this->configuration['options'],
      '#description' => t("Seleccione la categoría en la que se mostrará el lanzador."),
      '#default_value' => $this->configuration['category'],
      '#weight' => 10,
    ];
    $form['show_launcher'] = [
      '#type' => 'checkbox',
      '#title' => t('Mostrar lanzador'),
      '#weight' => 10,
      '#default_value' => $this->configuration['show'],
    ];
    $form['launcher'] = [
      '#type' => 'table',
      '#header' => [t('Field'), t('Label'), t('Show')],
      '#empty' => t('There are no items yet. Add an item.'),
      '#weight' => 10,
    ];

    $form['launcher']['title_launcher'] = [
      'title' => ['#plain_text' => t('Título del lanzador')],
      'title_value' => [
        '#type' => 'textfield',
        '#size' => 30,
        '#default_value' => $this->configuration['title_value'],
      ],
      'show_title' => [
        '#type' => 'checkbox',
        '#default_value' => $this->configuration['show_title'],
      ],
    ];
    $form['launcher']['description_launcher'] = [
      'description_title' => ['#plain_text' => t('Descripción del lanzador')],
      'description_value' => [
        '#type' => 'textfield',
        '#size' => 30,
        '#default_value' => $this->configuration['description_value'],
      ],
      'show_description' => [
        '#type' => 'checkbox',
        '#default_value' => $this->configuration['show_description'],
      ],
    ];
    $form['launcher']['url_launcher'] = [
      'url_title' => ['#plain_text' => t('Ruta de redirección')],
      'url_value' => [
        '#type' => 'textfield',
        '#size' => 30,
        '#default_value' => $this->configuration['url_value'],
      ],
    ];
    $form['launcher']['btn_launcher'] = [
      'btn_title' => ['#plain_text' => t('Título del botón')],
      'btn_value' => [
        '#type' => 'textfield',
        '#size' => 30,
        '#default_value' => $this->configuration['btn_value'],
      ],
      'show_btn' => [
        '#type' => 'checkbox',
        '#default_value' => $this->configuration['show_btn'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(LauncherBlock &$instance, &$config) {

    // Set values for duplicate cards.
    $this->instance = &$instance;
    $this->configuration = &$config;
    $this->instance->cardBuildHeader(FALSE, FALSE);

    $this->instance->setValue('directive', 'data-ng-launcher');
    $this->instance->setValue('config_name', 'launcherBlock');
    $this->instance->setValue('class', 'tbo-general-launcher');
    $categoryExist = FALSE;
    $launcher = ['show' => 0];
    $service = \Drupal::service('tbo_general.launcher');
    $category = '';
    if (isset($_SESSION['serviceDetail']['productId'])) {
      $category = $service->categoryByParanater($_SESSION['serviceDetail']['productId']);
      $category = $category->get('label');
      $categoryExist = TRUE;
    }

    $categorylauncher = $this->configuration['category'];
    $titleLauncher = isset($this->configuration['title_value']) ? $this->configuration['title_value'] : '';
    $titleLauncherShow = $this->configuration['show_title'];

    $descriptionLauncher = isset($this->configuration['description_value']) ? $this->configuration['description_value'] : '';
    $descriptionLauncherShow = $this->configuration['show_description'];

    $urlLauncher = isset($this->configuration['url_value']) ? $this->configuration['url_value'] : '';

    $btnLauncher = isset($this->configuration['btn_value']) ? $this->configuration['btn_value'] : '';
    $launcher['btn_show'] = 0;
    if ($titleLauncher !== '' && $titleLauncherShow === "1") {
      $launcher['title_show'] = 1;
      $launcher['title'] = t($titleLauncher);
    }
    if ($descriptionLauncher !== '' && $descriptionLauncherShow === "1") {
      $launcher['description_show'] = 1;
      $launcher['description'] = t($descriptionLauncher);
    }
    if ($descriptionLauncher !== '' && $descriptionLauncherShow === "1") {
      $launcher['description_show'] = 1;
      $launcher['description'] = t($descriptionLauncher);
    }
    if ($urlLauncher !== '' && $btnLauncher !== '') {
      $launcher['url'] = $urlLauncher;
      $launcher['btn_name'] = $btnLauncher;
      $launcher['btn_show'] = $this->configuration['show_btn'];
    }
    if ($this->configuration['show'] === 1) {
      $launcher['show'] = 1;
    }
    if ($categorylauncher !== 'empty' && $category !== $categorylauncher) {
      $launcher['show'] = 0;
    }

    $parameters = [
      'library' => 'tbo_general/card_launcher',
      'theme' => 'card_launcher',
    ];
    $others = [
      '#directive' => $this->instance->getValue('directive'),
      '#launcher' => $launcher,
    ];
    $this->instance->cardBuildVarBuild($parameters, $others);
    $other_config = [
      'categoryExist' => $categoryExist,
      'category' => $category
    ];
    $config_block = $this->instance->cardBuildConfigBlock('/tbo-general/launcher?_format=json', $other_config);
    $this->instance->cardBuildAddConfigDirective($config_block);

    return $this->instance->getValue('build');

  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['category'] = $form_state->getValue('category');
    $this->configuration['show'] = $form_state->getValue('show_launcher');

    $this->configuration['title_value'] = $form_state->getValue(['launcher', 'title_launcher', 'title_value']);
    $this->configuration['show_title'] = $form_state->getValue(['launcher', 'title_launcher', 'show_title']);

    $this->configuration['description_value'] = $form_state->getValue(['launcher', 'description_launcher', 'description_value']);
    $this->configuration['show_description'] = $form_state->getValue(['launcher', 'description_launcher', 'show_description']);

    $this->configuration['url_value'] = $form_state->getValue(['launcher', 'url_launcher', 'url_value']);

    $this->configuration['btn_value'] = $form_state->getValue(['launcher', 'btn_launcher', 'btn_value']);
    $this->configuration['show_btn'] = $form_state->getValue(['launcher', 'btn_launcher', 'show_btn']);

  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    $roles = $account->getRoles();
    if (in_array('administrator', $roles) || in_array('admin_company', $roles) || in_array('admin_group', $roles) || in_array('super_admin', $roles) || in_array('tigo_admin', $roles)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
