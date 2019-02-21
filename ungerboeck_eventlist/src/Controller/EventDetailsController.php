<?php
namespace Drupal\ungerboeck_eventlist\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ungerboeck_eventlist\Controller\Helpers;

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
    $module_config = \Drupal::config('ungerboeck_eventlist.settings');

    $buffer = Helpers::read_ungerboeck_file();
    $events_from_ungerboeck = json_decode(strip_tags($buffer), TRUE);
    $events_from_ungerboeck = array_reverse($events_from_ungerboeck);

    foreach ($events_from_ungerboeck as $event) {
      if ($event['EVENTID'] == $eventID) {
        $title = $event['EVENTDESCRIPTION'];

//if (empty($event['QUALTRICSID'])) {
        $results .= $this->handle_dates($event) . '<br />';
        $results .= $event['ANCHORVENUE'] . '<br />';
        $results .= $this->get_registration_info($event) . '<br />';
        $results .= $event['EVENTTYPECODE'] . '<br />';
  //continue;
//}

if (!empty($event['QUALTRICSID'])) {
$results .= '<hr />';
  $buffer = Helpers::hs_read_qualtrics_file();
  $events_from_qualtrics = json_decode(strip_tags($buffer), TRUE);
  $events_from_qualtrics['responses'] = array_reverse($events_from_qualtrics['responses']);
  foreach ($events_from_qualtrics['responses'] as $response) {
    if ($response['values']['_recordId'] == $event['QUALTRICSID']) {

// Found the right Qualtrics Response;
if ($event['EVENTTYPECODE'] == 'NONPRIORITY') {
  $results .= $this->handle_nonpriority_events($response);
}
else {
  $results .= $this->handle_priority_events($event, $response);
}
$results .= '<hr />';

//$results .= implode('<br />', $response['values']);
$results .= '<br /><strong>Raw Data:</strong><br />';
$results .= str_replace('%0A', ' ', str_replace('%28', '(', str_replace('%29', ')', str_replace('%2C', ',', str_replace('+', ' ', str_replace('%40', '@', str_replace('%2F', '/', str_replace('%3A', ':', str_replace('=', ' => ', str_replace('&', '<br />', http_build_query($response['values']))))))))))) . '<br />';
$results .= '<br />';
$results .= str_replace('%0A', ' ', str_replace('%28', '(', str_replace('%29', ')', str_replace('%2C', ',', str_replace('+', ' ', str_replace('%40', '@', str_replace('%2F', '/', str_replace('%3A', ':', str_replace('=', ' => ', str_replace('&', '<br />', http_build_query($response['labels']))))))))))) . '<br />';
      break;
    }
  }
}
else
{ $results .= 'No Qualtrics ID given<br />'; }
        break;
      }
    }

    $element = array(
      '#title' => $title,
      '#markup' => $results,
    );
    return $element;
  }

  private function get_registration_info($event) {
    $output = '';
    $now = time();
    $regstartdate = strtotime($event['REGDETAILSLIST'][0]['REGISTRATIONSTARTDATE']);
    $regenddate = strtotime($event['REGDETAILSLIST'][0]['REGISTRATIONENDDATE']);

    if (empty($event['REGDETAILSLIST'][0]['REGISTRATIONLINK'])) {
      $output = 'No online registration';
    } elseif ($now < $regstartdate) {
      $output = 'Registration opens ' . date('M d, Y', $regstartdate);
    } elseif ($now > $regenddate) {
      $output = 'Registration closed ' . date('M d, Y', $regenddate);
    }
    else {
      $output = '<a href="' . $event['REGDETAILSLIST'][0]['REGISTRATIONLINK'] . '">Register online</a>';
    }

    $output = '<span class="event_details_registration">' . $output . '</span>';
  
    return $output;
  }

  private function handle_dates($event) {
    $output = '';
    $startdate = Helpers::combine_date_time($event['EVENTSTARTDATE'], $event['EVENTSTARTTIME']);
    $enddate = Helpers::combine_date_time($event['EVENTENDDATE'], $event['EVENTENDTIME']);

    $output = date('l, m/d/Y', $startdate);
    if (date('Gi', $startdate) <> '0000') {
      $output .= date(' h:i A', $startdate);
    }

    $output .= ' - ';

    if (date('z', $startdate) <> date('z', $enddate)) {
      $output .= date(' m/d/y', $enddate);
    }

    if (date('Gi', $enddate) <> '0000') {
      $output .= date(' h:i A', $enddate);
    }

    $output = '<span class="event_details_dates">' . $output . '</span>';

    return $output;
  }

  private function handle_nonpriority_events($response) {
    $results = '';
    $results .= $response['values']['QID80_3'] . '<br />';
    $results .= $response['values']['QID80_5'] . '<br />';
    $results .= $response['values']['QID80_2'] . '<br />';
    $results .= $response['values']['QID80_4'] . '<br />';
    $results .= $response['values']['QID80_8'] . '<br />';

    return $results;
  }

  private function handle_priority_events($event, $response) {
    $results = '';
    if (!empty($response['values']['QID8_1'])) {
      $results .= $response['values']['QID8_1'] . '<br />';
      $results .= $response['values']['QID8_2'] . '<br />';
      $results .= $response['values']['QID8_3'] . ', ';
      $results .= $response['values']['QID8_4'] . ' ';
      $results .= $response['values']['QID8_5'] . '<br />';
    }
    if (!empty($response['values']['QID105_5'])) {
      $results .= $response['values']['QID105_5'] . '<br />';
      $results .= 'Instructor:<br />';
      $results .= $response['values']['QID105_4'] . '<br />';
      $results .= $response['values']['QID105_8'] . '<br />';
    }
    if (!empty($response['values']['QID19_1'])) {
      $results .= 'Contact Info:<br />';
      $results .= $response['values']['QID19_1'] . '<br />';
      if (!empty($response['values']['QID19_2'])) { $results .= $response['values']['QID19_2'] . '<br />'; }
      if (!empty($response['values']['QID19_3'])) { $results .= $response['values']['QID19_3'] . '<br />'; }
    }
    if (!empty($response['values']['QID10_1'])) {
      $results .= 'Instructor:<br />';
      $results .= $response['values']['QID10_1'] . '<br />';
      if (!empty($response['values']['QID10_2'])) { $results .= $response['values']['QID10_2'] . '<br />'; }
      if (!empty($response['values']['QID10_3'])) { $results .= $response['values']['QID10_3'] . '<br />'; }
    }
    if (!empty($response['values']['QID74_1_1'])) {
      $results .= 'Sessions:<br />';
      $i = 1;
      while (!empty($response['values']['QID74_1_' . $i])) {
        $results .= $response['values']['QID74_1_' . $i] . ' ';
        $results .= $response['values']['QID74_2_' . $i] . ' - ';
        $results .= $response['values']['QID74_3_' . $i] . '<br />';
        $i++;
      }
    }

    return $results;
  }

}

