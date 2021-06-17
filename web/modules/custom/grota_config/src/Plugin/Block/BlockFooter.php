<?php

namespace Drupal\grota_config\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'BlockFooter' block.
 *
 * @Block(
 *  id = "block_footer",
 *  admin_label = @Translation("Block Footer"),
 * )
 */
class BlockFooter extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $theme = [
      '#theme' => 'footer',
    ];
    $output = [
      '#type' => 'markup',
      '#markup' => \Drupal::service('renderer')->render($theme),
    ];
    return $output;
  }

}
