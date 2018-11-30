<?php

/**
 * @file
 * Contains \Drupal\ungerboeck_events\Plugin\Derivative\EventBlock.
 */

namespace Drupal\ungerboeck_events\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;

class EventBlock extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $config = \Drupal::config('ungerboeck_events.settings');
    $number_of_blocks = $config->get('number_of_blocks');

    for ($i=1; $i <= $number_of_blocks; $i++) {
     $this->derivatives[$i] = $base_plugin_definition;
      $this->derivatives[$i]['admin_label'] = t('Ungerboeck Events: ') . $i;
    }

    return $this->derivatives;
  }
}

