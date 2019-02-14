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
    $count = 0;
    $id = $this->getDerivativeID();
    $config = $this->getConfiguration();
    $module_config = \Drupal::config('ungerboeck_eventlist.settings');

    $max_events = intval($config['max_events']);
    if ($max_events == 0) {
      $max_events = PHP_INT_MAX;
    }

    $search_url = $module_config->get('url') . '/' . date('m-d-Y') . '/null/null/' . $config['account_number'];

    // Fetch the page
    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $search_url);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 1);
    //curl_setopt($curl_handle, CURLOPT_HEADER, 1);

    $buffer = "<?xml version='1.0' encoding='UTF-8'?>";

    $buffer .= curl_exec($curl_handle);
    curl_close($curl_handle);

    $json_events = json_decode(strip_tags($buffer), TRUE);
    $json_events = array_reverse($json_events);

    $results = '<ul class="ungerboeck_eventlist ungerboeck_eventlist_' .$id . '">';

    foreach ($json_events as $event) {
      $datetime = Helpers::combine_date_time($event['EVENTSTARTDATE'], $event['EVENTSTARTTIME']);
      if (date('Gi', $datetime) == '0000') {
        $datetimestr = date($config['format_without_time'], $datetime);
      } else {
        $datetimestr = date($config['format_with_time'], $datetime);
      }

      $title = $event['EVENTDESCRIPTION'];

      $results .= '<li>';
      $results .= '<a href="' . base_path() . 'event_details?eventID=' . $event['EVENTID'] .'&amp;acct=' . $config['account_number'] . '" class="event_title">' . $title . '</a><br/>';
      $results .= '<span class="event_venue">' . $event['ANCHORVENUE'] . '</span><br />';
      $results .= '<span class="event_date">' . $datetimestr . '</span><br/>';

      $results .= '</li>';
      $count++;
      if ($count >= $max_events) {
        break;
      }
    }

    $results .= '</ul>';

$results .= '<h1>' . $max_events . ':*' . $config['max_events'] . '*</h1>';
$results .= '<h1>' . count($json_events) . '</h1>';
$results .= '<h1>' . strlen($buffer) . '</h1>';
$results .= '<h1>' . $search_url . '</h1>';

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

    $form['account_number'] = array(
      '#type' => 'textfield',
      '#title' => t('Account Number'),
      '#description' => t('Account number of unit within the Ungerboeck system, Human Sciences is 00000150'),
      '#size' => 15,
      '#default_value' => $config['account_number'],
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
    $this->configuration['account_number'] = $values['account_number'];
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
      'account_number' => '00000150',
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

}
