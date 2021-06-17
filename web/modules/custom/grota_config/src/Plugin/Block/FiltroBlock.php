<?php

namespace Drupal\grota_config\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'FiltroBlock' block.
 *
 * @Block(
 *  id = "filtro_block",
 *  admin_label = @Translation("Filtro Block"),
 * )
 */
class FiltroBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $theme = [
      '#theme' => 'filtro_block',
    ];
    $output = [
      '#type' => 'markup',
      '#markup' => \Drupal::service('renderer')->render($theme),
    ];
    return $output;
  }

}
