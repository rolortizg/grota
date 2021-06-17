<?php


namespace Drupal\logos\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\logos\Entity\Logo;

/**
 * Bloque logo cabecera.
 *
 * @Block(
 *   id = "block_logo_header",
 *   admin_label = @Translation("Logo cabecera"),
 *   category = @Translation("Logo cabecera"),
 * )
 */
class BlockLogoHeader extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $url = NULL;
    $logo = Logo::load(Logo::TYPE_HEADER);
    if ($logo instanceof Logo) {
      $url = $logo->getUrl();
    }

    return [
      '#theme' => 'block_logo_header',
      '#url_logo' => $url,
    ];
  }

}
