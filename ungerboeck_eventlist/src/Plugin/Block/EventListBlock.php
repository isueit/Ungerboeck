<?php

namespace Drupal\ungerboeck_eventlist\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;


use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\ungerboeck_eventlist\Controller\Helpers;

/**
 * Provides a 'EventListBlock' block plugin.
 *
 * @Block(
 *   id = "eventlist_block",
 *   admin_label = @Translation("Event List block"),
 *   deriver = "Drupal\ungerboeck_eventlist\Plugin\Derivative\EventListBlock"
 * )
 */


class EventListBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Do NOT cache a page with this block on it.
    \Drupal::service('page_cache_kill_switch')->trigger();
 
    $results = "";
    $count = 0;
    $id = $this->getDerivativeID();
    $config = $this->getConfiguration();
    $module_config = \Drupal::config('ungerboeck_eventlist.settings');

    $max_events = intval($config['max_events']);
    if ($max_events == 0) {
      $max_events = PHP_INT_MAX;
    }

    $node = \Drupal::routeMatch()->getParameter('node');
    $string_of_search_terms = $this->build_search_string(!empty($node->field_ungerboeck_search_term->value) ? $node->field_ungerboeck_search_term->value : '', $config['title_search']);
    if (empty($string_of_search_terms)) {
      $show_all_events = TRUE;
      $search_terms = array ();
    } else {
      $show_all_events = FALSE;
      $search_terms = explode('|', $string_of_search_terms);
    }


    $buffer = Helpers::read_ungerboeck_file();

    $json_events = json_decode($buffer, TRUE);
    $json_events = array_reverse($json_events);

    $results .= PHP_EOL . '<ul class="ungerboeck_eventlist ungerboeck_eventlist_' .$id . '">' . PHP_EOL;

    foreach ($json_events as $event) {
      $title = $this->format_title($event);
      $display_event = FALSE;
      if ($show_all_events) {
        $display_event = TRUE;
      } else {
        foreach($search_terms as $search_term) {
          //$search_term = trim($search_term);
          if (!empty($search_term) &&  strpos(strtolower($title), $search_term) !== false) {
            $display_event = TRUE;
          }
        }
      }

      if ($display_event) {
        $startdatetimestr = $this->format_date_time(Helpers::combine_date_time($event['EVENTSTARTDATE'], $event['EVENTSTARTTIME']));

        $results .= '  <li>' . PHP_EOL;
        $results .= '    ' . $title . PHP_EOL;
        $results .= '    <div class="event_venue">' . $event['ANCHORVENUE'] . '</div>' . PHP_EOL;
        $results .= '    <div class="event_startdate">' . $startdatetimestr . '</div>' . PHP_EOL;

/* This is test code that should go away before we go live */
if (!empty($event['QUALTRICSID'])) {
  $results .= '    ' . $event['QUALTRICSID'] . '<br />' . PHP_EOL;
}
$results .= '    ' . $event['EVENTTYPECODE'] . '<br />' . PHP_EOL;

        $results .= '  </li>' . PHP_EOL;
        $count++;
        if ($count >= $max_events) {
          break;
        }
      }
    }

    $results .= '</ul>' . PHP_EOL;

    return [
      '#markup' => $this->t($results),
    ];
  }
  
  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account, $return_as_object = FALSE) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }


  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $form['max_events'] = array(
      '#type' => 'textfield',
      '#title' => t('Maximum Number of Events to display'),
      '#description' => t('Zero (0) means display all events'),
      '#size' => 15,
      '#default_value' => $config['max_events'],
    );

    $form['event_details_page'] = array(
      '#type' => 'checkbox',
      '#title' => t('Link to Details Page'),
      '#description' => t('When checked, every event title will link to a details page, otherwise, titles will link to registration pages where appropriate'),
      '#default_value' => $config['event_details_page'],
    );

    $form['format_with_time'] = array(
      '#type' => 'textfield',
      '#title' => t('Date/Time Format'),
      '#description' => t('Format of the date, see <a href="http://php.net/manual/en/function.date.php">php date manual</a>'),
      '#default_value' => $config['format_with_time'],
    );

    $form['format_without_time'] = array(
      '#type' => 'textfield',
      '#title' => t('Date Only Format'),
      '#description' => t('Use this format when the time is 12:00 am (midnight)'),
      '#default_value' => $config['format_without_time'],
    );

    $form['title_search'] = array(
      '#type' => 'textfield',
      '#title' => t('Restrict Search by title'),
      '#description' => t('Only show events with this search term in title, blank means show all events'),
      '#default_value' => $config['title_search'],
    );

    $form['placement'] = array(
      '#type' => 'textfield',
      '#title' => t('Placed on Page'),
      '#description' => t('Documentation: what page(s) the block is placed on'),
      '#size' => 75,
      '#maxlength' => 300,
      '#default_value' => $config['placement'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->configuration['event_details_page'] = $values['event_details_page'];
    $this->configuration['max_events'] = $values['max_events'];
    $this->configuration['format_with_time'] = $values['format_with_time'];
    $this->configuration['format_without_time'] = $values['format_without_time'];
    $this->configuration['title_search'] = $values['title_search'];
    $this->configuration['placement'] = $values['placement'];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'event_details_page' => TRUE,
      'max_events' => 0,
      'format_with_time' => 'M j, Y, g:i a',
      'format_without_time' => 'M j, Y',
      'title_search' => '',
      'placement' => '',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
      return 0;
  }

  /**
   * Format Date
   */
  private function format_date_time($datetime) {
    $config = $this->getConfiguration();

    if (date('Gi', $datetime) == '0000') {
      $datetimestr = date($config['format_without_time'], $datetime);
    } else {
      $datetimestr = date($config['format_with_time'], $datetime);
    }

    return $datetimestr;
  }

  /**
   * Format the title and get it ready to output
   */
  private function format_title ($event) {
    $config = $this->getConfiguration();

    $title = '<div class="event_title">';
    if ($config['event_details_page']) {
      $title .= '<a href="' . base_path() . 'event_details/' . $event['EVENTID'] . '">' . $event['EVENTDESCRIPTION'] . '</a>';
    } else {
      $now = time();
      $regstartdate = strtotime($event['REGDETAILSLIST'][0]['REGISTRATIONSTARTDATE']);
      $regenddate = strtotime($event['REGDETAILSLIST'][0]['REGISTRATIONENDDATE']);

      if (!empty($event['REGDETAILSLIST'][0]['REGISTRATIONLINK']) && ($now > $regstartdate && $now < $regenddate)) {
        $title .= '<a href="' . $event['REGDETAILSLIST'][0]['REGISTRATIONLINK'] . '">' . $event['EVENTDESCRIPTION'] . '</a>';
      } else {
        $title .= $event['EVENTDESCRIPTION'];
      }

    }
    $title .= '</div>';

    return $title;
  }

  /**
   * Combine two search strings into 1
   */
  private function build_search_string($str1, $str2) {
    $return_string = '';
    $str1 = trim(strtolower($str1));
    $str2 = trim(strtolower($str2));
    if (!empty($str1) && !empty($str2)) {
      $return_string = $str1 . '|' . $str2;
    } elseif (!empty($str1)) {
      $return_string = $str1;
    } else {
      $return_string = $str2;
    }

    return $return_string;
  }

}
