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
    $results = '';
    $title = 'Sorry, event not found';

    $eventID = intval(\Drupal::request()->query->get('eventID'));
    $account_number  = \Drupal::request()->query->get('acct');
    $module_config = \Drupal::config('ungerboeck_eventlist.settings');

    $account_number = $config['account_number'];
    $search_url = $module_config->get('url') . '/02-01-2019/null/null/' . $account_number;

    // Fetch the page
    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $search_url);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 1);
    //curl_setopt($curl_handle, CURLOPT_HEADER, 1);

    /*$buffer = "<?xml version='1.0' encoding='UTF-8'?>";*/

    $buffer .= curl_exec($curl_handle);
    curl_close($curl_handle);

    $json_events = json_decode(strip_tags($buffer), TRUE);
    $json_events = array_reverse($json_events);

    foreach ($json_events as $event) {
      if ($event['EVENTID'] == $eventID) {
        $title = $event['EVENTDESCRIPTION'];
        $results .= $event['ANCHORVENUE'] . '<br />';
        break;
      }
    }

    $element = array(
      '#title' => $title,
      '#markup' => $results,
    );
    return $element;
  }

}

