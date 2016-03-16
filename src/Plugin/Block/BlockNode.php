<?php

/**
 * @file
 * Contains \Drupal\block_node\Plugin\Block\BlockNode.
 */

namespace Drupal\block_node\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;

/**
 * Provides a block to view a specific entity.
 *
 * @Block(
 *   id = "block_node"
 * )
 */
class BlockNode extends BlockBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a new Node.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'view_mode' => 'default',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Add content'),
      '#options' => ['place' => $this->t('Place existing'), 'create' => $this->t('Create new')],
      '#default_value' => 'place',
    ];
//    $form['place'] = [
//      '#type' => 'container',
//      '#title' => $this->t('Place existing content'),
//
//    ];
    $form['node'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Select node'),
      '#states' => array(
        'visible' => array(
          ':input[name=mode]' => array('value' => 'place'),
        ),
      ),
    ];
    $form['view_mode'] = [
      '#type' => 'select',
      '#options' => $this->entityManager->getViewModeOptions('node'),
      '#title' => $this->t('View mode'),
      '#default_value' => $this->configuration['view_mode'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['view_mode'] = $form_state->getValue('view_mode');
    $this->configuration['node'] = $form_state->getValue('node');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var $entity \Drupal\Core\Entity\EntityInterface */
    dpm($this->configuration);
    $nid = $this->configuration['node'];
    $node = Node::load($nid);
    //$entity_manager->getFormObject($entity_type, $operation)
    //$entity = node_load(3);
    //$entity = $entity_manager->getStorage($entity_manager->getEntityTypeFromClass(get_called_class()))->load($nid);
    //$entity = $this->getContextValue('entity');

    $view_builder = $this->entityManager->getViewBuilder('node');
    $build = $view_builder->view($node, $this->configuration['view_mode']);

    //CacheableMetadata::createFromObject($this->getContext('entity'))->applyTo($build);

    return $build;
  }

}
