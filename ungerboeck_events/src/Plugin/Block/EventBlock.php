<?php

namespace Drupal\ungerboeck_events\Plugin\Block;

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
 * Provides a 'EventBlock' block plugin.
 *
 * @Block(
 *   id = "event_block",
 *   admin_label = @Translation("Event block"),
 *   deriver = "Drupal\ungerboeck_events\Plugin\Derivative\EventBlock"
 * )
 */


class EventBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
$startblock = microtime(TRUE);
    $id = $this->getDerivativeID();
    $config = $this->getConfiguration();
$starttime = microtime(TRUE);
    $token = ungerboeck_helpers_get_token();
$endtime = microtime(TRUE);
\Drupal::logger('ungerboeck_events')->notice($endtime - $starttime . ': Get token');

$starttime = $endtime;
    $event_list = ungerboeck_helpers_get_event_list($config['orgcode'], $config['search_string'], $token);
$endtime = microtime(TRUE);
\Drupal::logger('ungerboeck_events')->notice($endtime - $starttime . ': Get events');


    $json_events = json_decode(strip_tags($event_list), TRUE);

    $results = '<ul class="ungerboeck_events ungerboeck_events_' .$id . '">';

    foreach ($json_events as $event) {
      $time = strtotime(ungerboeck_helpers_combine_date_time($event['StartDate'], $event['StartTime']));
      $title = $event['Description'];
      $webaddress = $event['WebAddress'];

//      if ($event['WebAddress'] == '') {
//$starttime = $endtime;
//        $event_details = json_decode(strip_tags(ungerboeck_helpers_get_event_details($config['orgcode'], $event['EventID'], $token)), TRUE);
//$endtime = microtime(TRUE);
//\Drupal::logger('ungerboeck_events')->notice($endtime - $starttime . ': ' . $event['EventID']);
//        $webaddress = $event_details['EventUserFieldSets'][0]['UserText01'];
//      }

      $results .= '<li>';

      // Handle web address
      if (!empty($webaddress)) {
        $results .= '<a href="' . $webaddress . '">' . $title;
        $results .= '</a><br/>';
      }
      else {
        $results .= $title . '<br/>';
      }

      $results .= date($config['format'], $time) . '<br/>';
$results .= $event['EventID'] . '<br/>';
      $results .= '</li>';
    }

    $results .= '</ul>';
$endblock = microtime(TRUE);
\Drupal::logger('ungerboeck_events')->notice($endblock - $startblock . ': Block build time');

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
//    $form['ungerboeck_fieldset'] = array(
//      '#type' => 'fieldset',
//      '#collaspible' => TRUE,
//      '#collasped' => TRUE,
//    );

//    $form['ungerboeck_fieldset']['orgcode'] = array(
    $form['orgcode'] = array(
      '#type' => 'textfield',
      '#title' => t('OrgCode'),
      '#description' => t('For ISU, this is usually 10'),
      '#size' => 15,
      '#default_value' => $config['orgcode'],
    );

//    $form['ungerboeck_fieldset']['search_string'] = array(
    $form['search_string'] = array(
      '#type' => 'textfield',
      '#title' => t('Search String'),
      '#description' => t('This is the OData search string.<br/>Note: #TodayDate# will be replaced by the actual date'),
      '#size' => 175,
      '#maxlength' => 300,
      '#default_value' => $config['search_string'],
    );
    $form['format'] = array(
      '#type' => 'textfield',
      '#title' => t('Date Format'),
      '#description' => t('Format of the date, see <a href="http://php.net/manual/en/function.date.php">php date manual</a>'),
      '#default_value' => $config['format'],
    );

    $form['placement'] = array(
      '#type' => 'textfield',
      '#title' => t('Placed on Page'),
      '#description' => t('Optional, page this block is placed on'),
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
    $this->configuration['orgcode'] = $values['orgcode'];
    $this->configuration['search_string'] = $values['search_string'];
    $this->configuration['format'] = $values['format'];
    $this->configuration['placement'] = $values['placement'];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'orgcode' => '10',
      'search_string' => 'Account eq \'00000150\' and Status eq \'30\' and StartDate ge DateTime\'#TodayDate#\'$orderby=StartDate,StartTime$page_size=4',
      'format' => 'l, F j, Y',
      'placement' => '',
    );
  }

}
