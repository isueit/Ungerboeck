<?php
namespace Drupal\ungerboeck_eventlist\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ungerboeck_eventlist\Controller\Helpers;

/**
 * Provides helper methods for the ungerboeck_eventlist module. That is, methods here may be called from
 * multiple classes within the ungerboeck_eventlist module.
 *
 */
class Helpers extends ControllerBase {

  /*
   * This method may be deleted, I think!
   */
  public function handle_dates($event) {
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

    //$output = '<span class="event_details_dates">' . $output . '</span>';
    $output = '<span class="event_details_dates">' . 'This should get displayed!' . '</span>';

    return $output;
  }

  /* 
   * Combine separate date and time variables into one variable
   */
  public function combine_date_time($datepart, $timepart) {
    return strtotime(date('m/d/y', strtotime($datepart)) . ' ' . date('H:i', strtotime($timepart)));
  }
}

