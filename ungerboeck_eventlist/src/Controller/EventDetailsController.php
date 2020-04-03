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

        $title = $event['EVENTDESCRIPTION'];
        $results .= $this->handle_dates($event) . PHP_EOL;

        if (empty($event['QUALTRICSID'])) {
          $results .= '  <div class="event_location">' . $event['ANCHORVENUE'] . '</div>' . PHP_EOL;
        } else {

          $buffer = Helpers::hs_read_qualtrics_file();
          $events_from_qualtrics = json_decode($buffer, TRUE);
          $events_from_qualtrics['responses'] = array_reverse($events_from_qualtrics['responses']);
          foreach ($events_from_qualtrics['responses'] as $survey_response) {
            if ($survey_response['values']['_recordId'] == $event['QUALTRICSID']) {

              // Found the right Qualtrics Response;
              $results .= $this->get_event_address($survey_response);
              $results .= $this->get_event_location($survey_response);
              $results .= $this->get_event_description($survey_response, $event['EVENTTYPECODE'] !== 'NONPRIORITY');
              $results .= $this->get_event_contact($survey_response);
              $results .= $this->get_event_instructor($survey_response);
              $results .= $this->get_event_sessions($survey_response);
              break;
            }
          }
        }
        $results .= $this->get_registration_info($event) . PHP_EOL;

        // We've found the correct event, quit looking for the right event
        break;
      }
    }
    $results .= PHP_EOL . '</div>' . PHP_EOL;

    $element = array(
      '#title' => $title,
      '#markup' => $results,
      '#attached' => [ 'library' => ['ungerboeck_eventlist/ungerboeck_eventlist']],
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

  private function get_event_address($survey_response) {
    $event_address = '';

    if (!empty($survey_response['values']['QID8_1'])) { $event_address .= '    ' . $survey_response['values']['QID8_1'] . '<br />' . PHP_EOL; }
    if (!empty($survey_response['values']['QID8_2'])) {
      if (substr($survey_response['values']['QID8_2'], 0, 7) === 'http://' || substr($survey_response['values']['QID8_2'], 0, 8) === 'https://') {
        $event_address .= '<a href="' . trim($survey_response['values']['QID8_2']) . '">' . trim($survey_response['values']['QID8_2']) . '</a><br />' . PHP_EOL;
      } else {
        $event_address .= $survey_response['values']['QID8_2'] . '<br />' . PHP_EOL;
      }
    }
    if (!empty($survey_response['values']['QID8_3'])) { $event_address .= '    ' . $survey_response['values']['QID8_3'] . ', '; }
    if (!empty($survey_response['values']['QID8_4'])) { $event_address .= $survey_response['values']['QID8_4'] . ' '; }
    if (!empty($survey_response['values']['QID8_5'])) { $event_address .= $survey_response['values']['QID8_5'] . '<br />' . PHP_EOL; }
    //if (!empty($survey_response['values']['QID8_6'])) { $event_address .= str_replace("\n", '<br />', $survey_response['values']['QID8_6']) . PHP_EOL; }

    if (!empty($event_address)) {
      $event_address = '  <div class="event_address">' . $event_address . '  </div>' . PHP_EOL;
    }
    return $event_address;
  }

  private function get_event_description($survey_response, $is_priority_program) {
    $event_description = '';
    $list_of_descriptions = json_decode(Helpers::hs_read_descriptions_file(), TRUE);

    if ($is_priority_program) {
      $event_description = str_replace(' target="_blank"', '', $list_of_descriptions[$survey_response['values']['QID1']]);
    } elseif (!empty($survey_response['values']['QID80_2'])) {
      $event_description = $survey_response['values']['QID80_2'];
    } else {
      $event_description = $survey_response['values']['QID22_2'];
    }

    if (!empty($event_description)) {
      $event_description = '  <div class="event_description">' . $event_description . '</div>' . PHP_EOL;
    }
    return $event_description;
  }

  private function get_event_location($survey_response) {
    $event_location = '';

    if (!empty($survey_response['values']['QID105_5'])) {
      $event_location = $survey_response['values']['QID105_5'];
    } else {
      $event_location = $survey_response['values']['QID80_5'];
    }
    if (!empty($event_location)) {
      $event_location = '  <div class="event_location">' . $event_location . '</div>' . PHP_EOL;
    }

    return $event_location;
  }

  private function get_event_contact($survey_response) {
    $event_contact = '';

    if (!empty($survey_response['values']['QID19_1'])) {
      $event_contact .= '  <div class="event_contact_label">Contact Info:</div>' . PHP_EOL;
      $event_contact .= '  <div class="event_contact_name">' . $survey_response['values']['QID19_1'] . '</div>' . PHP_EOL;
      if (!empty($survey_response['values']['QID19_2'])) { $event_contact .= '  <div class="event_contact_email"><a href="mailto:' . $survey_response['values']['QID19_2'] . '">' . $survey_response['values']['QID19_2'] . '</a></div>' . PHP_EOL; }
      if (!empty($survey_response['values']['QID19_3'])) { $event_contact .= '  <div class="event_contact_phone">' . $survey_response['values']['QID19_3'] . '</div>' . PHP_EOL; }
    } elseif (!empty($survey_response['values']['QID80_4'])) {
      $event_contact .= '  <div class="event_contact_label_name">Contact Info:</div>' . PHP_EOL;
      $event_contact .= '  <div class="event_contact">' . $survey_response['values']['QID80_4'] . '</div>' . PHP_EOL;
      $event_contact .= '  <div class="event_contact_email"><a href="mailto://' . $survey_response['values']['QID80_8'] . '">' . $survey_response['values']['QID80_8'] . '</a></div>' . PHP_EOL;
    }

    if (!empty($event_contact)) {
      $event_contact = '  <div class="event_contact">' . $event_contact . '</div>' . PHP_EOL;
    }

    return $event_contact;
  }

  private function get_event_instructor($survey_response) {
    $event_instructor = '';

    if (!empty($survey_response['values']['QID105_5'])) {
      $event_instructor .= '  <div class="event_instructor_label">' . 'Instructor:</div>' . PHP_EOL;
      $event_instructor .= '  <div class="event_instructor_name">' . $survey_response['values']['QID105_4'] . '</div>' . PHP_EOL;
      $event_instructor .= '  <div class="event_instructor_email"><a href="mailto:' . $survey_response['values']['QID105_8'] . '">' . $survey_response['values']['QID105_8'] . '</a></div>' . PHP_EOL;
    } elseif (!empty($survey_response['values']['QID10_1'])) {
      $event_instructor .= '  <div class="event_instructor_label">Instructor:</div>' . PHP_EOL;
      $event_instructor .= '  <div class="event_instructor_name">' . $survey_response['values']['QID10_1'] . '</div>' . PHP_EOL;
      if (!empty($survey_response['values']['QID10_2'])) { $event_instructor .= '  <div class="event_instructor_email"><a href="mailto:' . $survey_response['values']['QID10_2'] . '">' . $survey_response['values']['QID10_2'] . '</a></div>' . PHP_EOL; }
      if (!empty($survey_response['values']['QID10_3'])) { $event_instructor .= '  <div class="event_instructor_phone">' . $survey_response['values']['QID10_3'] . '</div>' . PHP_EOL; }
    }

    if (!empty($event_instructor)) {
      $event_instructor = '  <div class="event_instructor">' . $event_instructor . '</div>' . PHP_EOL;
    }

    return $event_instructor;
  }

  private function get_event_sessions($survey_response) {
    $event_sessions = '';

    if (!empty($survey_response['values']['QID74_1_1'])) {
      $event_sessions .= '  <div class="event_sessions_label">Sessions:</div>' . PHP_EOL;
      $i = 1;
      while (!empty($survey_response['values']['QID74_1_' . $i])) {
        $event_sessions .= '  <div class="event_session_names">' . $survey_response['values']['QID74_1_' . $i] . ' ';
        $event_sessions .= $survey_response['values']['QID74_2_' . $i] . ' - ';
        $event_sessions .= $survey_response['values']['QID74_3_' . $i]. '</div>' . PHP_EOL;
        $i++;
      }
    }

    if (!empty($event_sessions)) {
      $event_sessions = '  <div class="event_sessions">' . $event_sessions . '</div>' . PHP_EOL;
    }

    return $event_sessions;
  }
}
