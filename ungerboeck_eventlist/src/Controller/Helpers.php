<?php
namespace Drupal\ungerboeck_eventlist\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ungerboeck_eventlist\Controller\Helpers;

/**
 * Provides helper methods for the ungerboeck_eventlist module. That is, methods here may be called from
 * multiple classes within the ungerboeck_eventlist module.
 *
 */
class Helpers {

  /* 
   * Combine separate date and time variables into one variable
   */
  public function combine_date_time($datepart, $timepart) {
    return strtotime(date('m/d/y', strtotime($datepart)) . ' ' . date('H:i', strtotime($timepart)));
  }
}

