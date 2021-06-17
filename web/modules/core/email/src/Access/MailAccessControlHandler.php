<?php


namespace Drupal\email\Access;


use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

class MailAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\email\Entity\Mail $entity */

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view mail entities');
      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit mail entities');
      case 'delete':
        if (\Drupal::currentUser()->id() == 1) {
          return AccessResult::allowed();
        }
        else {
          return AccessResult::forbidden();
        }
    }

    return AccessResult::neutral();
  }

}
