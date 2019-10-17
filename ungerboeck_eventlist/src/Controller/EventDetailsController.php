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
  public function event_details($eventID) {
    $results = '';
    $results .= PHP_EOL . '<div class="ungerboeck_eventlist ungerboeck_eventlist_details">' . PHP_EOL;
    $title = 'Sorry, event not found';

    $eventID = intval($eventID);
    $module_config = \Drupal::config('ungerboeck_eventlist.settings');

    $buffer = Helpers::read_ungerboeck_file();
    $events_from_ungerboeck = json_decode(strip_tags($buffer), TRUE);
    $events_from_ungerboeck = array_reverse($events_from_ungerboeck);


    foreach ($events_from_ungerboeck as $event) {
      if ($event['EVENTID'] == $eventID) {

// ** Delete all lines with $extra_html before going live. This output is just for testing, not production **
//$extra_html = '<hr />';
//$extra_html .= '<strong>Raw Data:</strong><br />';

        $title = $event['EVENTDESCRIPTION'];
        $results .= $this->handle_dates($event) . PHP_EOL;

        if (empty($event['QUALTRICSID'])) {
          $results .= '  <div class="event_location">' . $event['ANCHORVENUE'] . '</div>' . PHP_EOL;
// ** Delete all lines with $extra_html before going live. This output is just for testing, not production **
//$extra_html .= $event['EVENTTYPECODE'] . '<br />';
//$extra_html .= 'No Qualtrics ID given<br />';
        } else {

          $buffer = Helpers::hs_read_qualtrics_file();
          $events_from_qualtrics = json_decode($buffer, TRUE);
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

// ** Delete all lines with $extra_html before going live. This output is just for testing, not production **
//$extra_html .= str_replace('%0A', ' ', str_replace('%28', '(', str_replace('%29', ')', str_replace('%2C', ',', str_replace('+', ' ', str_replace('%40', '@', str_replace('%2F', '/', str_replace('%3A', ':', str_replace('=', ' => ', str_replace('&', '<br />', http_build_query($response['values']))))))))))) . '<br />';
//$extra_html .= '<br />';
//$extra_html .= str_replace('%0A', ' ', str_replace('%28', '(', str_replace('%29', ')', str_replace('%2C', ',', str_replace('+', ' ', str_replace('%40', '@', str_replace('%2F', '/', str_replace('%3A', ':', str_replace('=', ' => ', str_replace('&', '<br />', http_build_query($response['labels']))))))))))) . '<br />';
//$extra_html .= '<br />';
//$extra_html .= str_replace('%0A', ' ', str_replace('%28', '(', str_replace('%29', ')', str_replace('%2C', ',', str_replace('+', ' ', str_replace('%40', '@', str_replace('%2F', '/', str_replace('%3A', ':', str_replace('=', ' => ', str_replace('&', '<br />', http_build_query($response['displayedFields']))))))))))) . '<br />';
//$extra_html .= '<br />';
//$extra_html .= str_replace('%0A', ' ', str_replace('%28', '(', str_replace('%29', ')', str_replace('%2C', ',', str_replace('+', ' ', str_replace('%40', '@', str_replace('%2F', '/', str_replace('%3A', ':', str_replace('=', ' => ', str_replace('&', '<br />', http_build_query($response['displayedValues']))))))))))) . '<br />';

              break;
            }
          }
        }
        $results .= $this->get_registration_info($event) . PHP_EOL;

// ** Delete all lines with $extra_html before going live. This output is just for testing, not production **
//$extra_html .= '<br />';
//$extra_html .= str_replace('%5B', '[', str_replace('%5D', ']', str_replace('%0A', ' ', str_replace('%28', '(', str_replace('%29', ')', str_replace('%2C', ',', str_replace('+', ' ', str_replace('%40', '@', str_replace('%2F', '/', str_replace('%3A', ':', str_replace('=', ' => ', str_replace('&', '<br />', http_build_query($event))))))))))))) . '<br />';
//$results .= $extra_html;

        // We've found the correct event, quit looking for the right event
        break;
      }
    }
    $results .= PHP_EOL . '</div>' . PHP_EOL;

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

    $output = '  <div class="event_details_registration">' . $output . '</div>';
  
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

    $output = '  <div class="event_details_dates">' . $output . '</div>';

    return $output;
  }

  private function handle_nonpriority_events($response) {
    $results = '';
    $results .= '  <div class="event_location">' . $response['values']['QID80_5'] . '</div>' . PHP_EOL;
    $results .= '  <div class="event_description">' . $response['values']['QID80_2'] . '</div>' . PHP_EOL;
    $results .= '  <div class="event_contact_label">Contact Info:</div>' . PHP_EOL;
    $results .= '  <div class="event_contact">' . $response['values']['QID80_4'] . '</div>' . PHP_EOL;
    $results .= '  <div class="event_contact_email"><a href="mailto://' . $response['values']['QID80_8'] . '">' . $response['values']['QID80_8'] . '</a></div>' . PHP_EOL;

    return $results;
  }

  private function handle_priority_events($event, $response) {
    $list_of_descriptions = json_decode(Helpers::hs_read_descriptions_file(), TRUE);

    $results = '';
    $mydescription = '  <div class="event_description">' . str_replace(' target="_blank"', '', $list_of_descriptions[$response['values']['QID1']]) . '</div>';

    if (!empty($response['values']['QID8_1'])) {
      $results .= '  <div class="event_address">' . $response['values']['QID8_1'] . '<br />' . PHP_EOL;
      if (!empty($response['values']['QID8_2'])) { $results .= '    ' . $response['values']['QID8_2'] . '<br />' . PHP_EOL; }
      if (!empty($response['values']['QID8_3'])) { $results .= '    ' . $response['values']['QID8_3'] . ', '; }
      if (!empty($response['values']['QID8_4'])) { $results .= $response['values']['QID8_4'] . ' '; }
      if (!empty($response['values']['QID8_5'])) { $results .= $response['values']['QID8_5']; }
      $results .= '  </div>' . PHP_EOL;
      $results .= $mydescription . PHP_EOL;
    }

    if (!empty($response['values']['QID105_5'])) {
      $results .= '  <div class="event_location">' . $response['values']['QID105_5'] . '</div>' . PHP_EOL;
      $results .= $mydescription . PHP_EOL;
      $results .= '  <div class="event_instructor_label">' . 'Instructor:</div>' . PHP_EOL;
      $results .= '  <div class="event_instructor">' . $response['values']['QID105_4'] . '</div>' . PHP_EOL;
      $results .= '  <div class="event_instructor_email"><a href="mailto://' . $response['values']['QID105_8'] . '">' . $response['values']['QID105_8'] . '</a></div>' . PHP_EOL;
    }

    if (!empty($response['values']['QID19_1'])) {
      $results .= '  <div class="event_contact_label">Contact Info:</div>' . PHP_EOL;
      $results .= '  <div class="event_contact">' . $response['values']['QID19_1'] . '</div>' . PHP_EOL;
      if (!empty($response['values']['QID19_2'])) { $results .= '  <div class="event_contact_email"><a href="' . $response['values']['QID19_2'] . '">' . $response['values']['QID19_2'] . '</a></div>' . PHP_EOL; }
      if (!empty($response['values']['QID19_3'])) { $results .= '  <div class="event_contact_phone">' . $response['values']['QID19_3'] . '</div>' . PHP_EOL; }
    }

    if (!empty($response['values']['QID10_1'])) {
      $results .= '  <div class="event_instructor_label">Instructor:</div>' . PHP_EOL;
      $results .= '  <div class="event_instructor">' . $response['values']['QID10_1'] . '</div>' . PHP_EOL;
      if (!empty($response['values']['QID10_2'])) { $results .= '  <div class="event_instructor_email"><a href="' . $response['values']['QID10_2'] . '">' . $response['values']['QID10_2'] . '</a></div>' . PHP_EOL; }
      if (!empty($response['values']['QID10_3'])) { $results .= '  <div class="event_instructor_phone">' . $response['values']['QID10_3'] . '</div>' . PHP_EOL; }
    }

    if (!empty($response['values']['QID74_1_1'])) {
      $results .= '  <div class="event_sessions_label">Sessions:</div>' . PHP_EOL;
      $i = 1;
      while (!empty($response['values']['QID74_1_' . $i])) {
        $results .= '  <div class="event_session">' . $response['values']['QID74_1_' . $i] . ' ';
        $results .= $response['values']['QID74_2_' . $i] . ' - ';
        $results .= $response['values']['QID74_3_' . $i]. '</div>' . PHP_EOL;
        $i++;
      }
    }

    return $results;
  }

}

