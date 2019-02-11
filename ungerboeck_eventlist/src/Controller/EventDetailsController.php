<?php
namespace Drupal\ungerboeck_eventlist\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the ungerboeck_eventlist  module.
 */
class EventDetailsController extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function event_details() {
    $output = 'Hello World<br />';

    $output .= \Drupal::request()->query->get('eventID');
    $element = array(
      '#markup' => $output,
    );
    return $element;
  }

}

