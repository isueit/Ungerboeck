<?php

/**
 * @file
 * Contains \Drupal\ungerboeck_listevents\Plugin\Derivative\EventListBlock.
 */

namespace Drupal\ungerboeck_eventlist\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;

class EventListBlock extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $config = \Drupal::config('ungerboeck_eventlist.settings');
    $number_of_blocks = $config->get('number_of_blocks');

    for ($i=1; $i <= $number_of_blocks; $i++) {
     $this->derivatives[$i] = $base_plugin_definition;
      $this->derivatives[$i]['admin_label'] = t('Ungerboeck Event List: ') . $i;
    }

    return $this->derivatives;
  }
}

