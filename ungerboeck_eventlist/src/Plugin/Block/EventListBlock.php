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
//$startblock = microtime(TRUE);
    $id = $this->getDerivativeID();
    $config = $this->getConfiguration();
$module_config = \Drupal::config('ungerboeck_eventlist.settings');

#$search_url = $config['url'] . '/' . date('m-d-Y') . '/null/null/' . $config['account_number'];
$search_url = $module_config->get('url') . '/02-01-2019/null/null/' . $config['account_number'];

  // Fetch the page
  $curl_handle = curl_init();
  curl_setopt($curl_handle, CURLOPT_URL, $search_url);
  //curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array('token: ' . $token));
  curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 1);
  //curl_setopt($curl_handle, CURLOPT_HEADER, 1);

$buffer = "<?xml version='1.0' encoding='UTF-8'?>";

  $buffer .= curl_exec($curl_handle);
  curl_close($curl_handle);


//$starttime = microtime(TRUE);

    //$token = ungerboeck_helpers_get_token();
//$endtime = microtime(TRUE);
//\Drupal::logger('ungerboeck_eventlist')->notice($endtime - $starttime . ': Get token');

//$starttime = $endtime;
    //$event_list = ungerboeck_helpers_get_event_list($config['orgcode'], $config['search_string'], $token);
//$endtime = microtime(TRUE);
//\Drupal::logger('ungerboeck_eventlist')->notice($endtime - $starttime . ': Get events');


    //$json_events = json_decode(strip_tags($event_list), TRUE);

    //$results = '<ul class="ungerboeck_eventlist ungerboeck_eventlist_' .$id . '">';

    //foreach ($json_events as $event) {
      //$time = strtotime(ungerboeck_helpers_combine_date_time($event['StartDate'], $event['StartTime']));
      //$title = $event['Description'];
      //$webaddress = $event['WebAddress'];

//      if ($event['WebAddress'] == '') {
//$starttime = $endtime;
//        $event_details = json_decode(strip_tags(ungerboeck_helpers_get_event_details($config['orgcode'], $event['EventID'], $token)), TRUE);
//$endtime = microtime(TRUE);
//\Drupal::logger('ungerboeck_events')->notice($endtime - $starttime . ': ' . $event['EventID']);
//        $webaddress = $event_details['EventUserFieldSets'][0]['UserText01'];
//      }

      //$results .= '<li>';

      // Handle web address
      //if (!empty($webaddress)) {
        //$results .= '<a href="' . $webaddress . '">' . $title;
        //$results .= '</a><br/>';
      //}
      //else {
        //$results .= $title . '<br/>';
      //}

      //$results .= date($config['format'], $time) . '<br/>';
      //$results .= '</li>';
    //}

//$endblock = microtime(TRUE);
//\Drupal::logger('ungerboeck_eventlist')->notice($endblock - $startblock . ': Block build time');


    $json_events = json_decode(strip_tags($buffer), TRUE);
$json_events = array_reverse($json_events);


    $results = '<ul class="ungerboeck_eventlist ungerboeck_eventlist_' .$id . '">';

    foreach ($json_events as $event) {

$location_date = strpos($event['EVENTSTARTDATE'], ' ');
$location_time = strpos($event['EVENTSTARTTIME'], ' ');

$time = strtotime(substr($event['EVENTSTARTDATE'], 0, $location_date) . substr($event['EVENTSTARTTIME'], $location_time));


      //$time = strtotime(ungerboeck_helpers_combine_date_time($event['StartDate'], $event['StartTime']));
      $title = $event['EVENTDESCRIPTION'];
      //$webaddress = $event['WebAddress'];

//      if ($event['WebAddress'] == '') {
//$starttime = $endtime;
//        $event_details = json_decode(strip_tags(ungerboeck_helpers_get_event_details($config['orgcode'], $event['EventID'], $token)), TRUE);
//$endtime = microtime(TRUE);
//\Drupal::logger('ungerboeck_events')->notice($endtime - $starttime . ': ' . $event['EventID']);
//        $webaddress = $event_details['EventUserFieldSets'][0]['UserText01'];
//      }

      $results .= '<li>';

      // Handle web address
      //if (!empty($webaddress)) {
        //$results .= '<a href="' . $webaddress . '">' . $title;
        //$results .= '</a><br/>';
      //}
      //else {
        $results .= '<a href="' . base_path() . 'event_details?eventID=' . $event['EVENTID'] .'" class="event_title">' . $title . '</a><br/>';
      //}

      $results .= '<span class="event_venue">' . $event['ANCHORVENUE'] . '</span><br />';
      $results .= '<span class="event_date">' .date($config['format'], $time) . '</span><br/>';

      $results .= '</li>';
    }
    $results .= '</ul>';

//$error = 'none';
//$xml = simplexml_load_string($buffer) or die('Can\'t load XML');

//$results .= $error;
//$results .= $buffer;
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
    $form['account_number'] = array(
      '#type' => 'textfield',
      '#title' => t('Account Number'),
      '#description' => t('Account number of unit within the Ungerboeck system, Human Sciences is 00000150'),
      '#size' => 15,
      '#default_value' => $config['account_number'],
    );

/*    $form['search_string'] = array(
      '#type' => 'textfield',
      '#title' => t('Search String'),
      '#description' => t('This is the OData search string.<br/>Note: #TodayDate# will be replaced by the actual date'),
      '#size' => 175,
      '#maxlength' => 300,
      '#default_value' => $config['search_string'],
    );
*/

/*    $form['url'] = array(
      '#type' => 'textfield',
      '#title' => t('URL of Events Feeds'),
      '#description' => t('Base URL of the feed from Ungerboeck'),
      '#size' => 175,
      '#maxlength' => 300,
      '#default_value' => $config['url'],
    );
*/

    $form['format'] = array(
      '#type' => 'textfield',
      '#title' => t('Date Format'),
      '#description' => t('Format of the date, see <a href="http://php.net/manual/en/function.date.php">php date manual</a>'),
      '#default_value' => $config['format'],
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

    //$this->configuration['ungerboeck_servsafe_settings'] = $form_state->getValue('ungerboeck_servsafe_settings');
    $this->configuration['account_number'] = $values['account_number'];
    //$this->configuration['search_string'] = $values['search_string'];
    //$this->configuration['url'] = $values['url'];
    $this->configuration['format'] = $values['format'];
    $this->configuration['title_search'] = $values['title_search'];
    $this->configuration['placement'] = $values['placement'];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'account_number' => '00000150',
      //'search_string' => 'Account eq \'00000150\' and Status eq \'30\' and StartDate ge DateTime\'#TodayDate#\'$orderby=StartDate,StartTime$page_size=4',
      //'url' => 'https://iebms.extension.iastate.edu/RegistrationCalendarWebService/Api/CalendarEvents/GetCalendarEvents',
      'format' => 'l, F j, Y',
      'title_search' => '',
      'placement' => '',
    );
  }

}
