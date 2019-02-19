<?php
namespace Drupal\ungerboeck_eventlist\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ungerboeck_eventlist\Controller\Helpers;

/**
 * Provides helper methods for the ungerboeck_eventlist module. That is, methods here may be called from
 * multiple classes within the ungerboeck_eventlist module.
 *
 */
class Helpers {

  /* 
   * Combine separate date and time variables into one variable
   */
  public static function combine_date_time($datepart, $timepart) {
    return strtotime(date('m/d/y', strtotime($datepart)) . ' ' . date('H:i', strtotime($timepart)));
  }

  /*
   * Trim trailing slashes
   */
  public static function trim_slash($inputstr) {
    return rtrim($inputstr, '/');
  }


  /*
   * Return the contents of the file that is storing the events from Ungerboeck
   */
  public static function read_ungerboeck_file() {
    $returnstr = '';
    $path_to_file = \Drupal::service('file_system')->realpath(file_default_scheme() . "://") . '/ungerboeck_eventlist/ungerboeck.json';

    if (!file_exists($path_to_file) || (date('z', time()) <> date('z', filemtime($path_to_file))) || filesize($path_to_file) == 0) {
      Helpers::create_ungerboeck_file();
    }

    $myfile = fopen($path_to_file, 'r') or die('Unable to open file!');
    $returnstr = fread($myfile, filesize($path_to_file));
    fclose($myfile);
    return $returnstr;
  }

  /*
   * Get the list of events from Ungerboeck, and save it in the files folder
   */
  public static function create_ungerboeck_file() {
    $module_config = \Drupal::config('ungerboeck_eventlist.settings');

    $path_to_folder = \Drupal::service('file_system')->realpath(file_default_scheme() . "://") . '/ungerboeck_eventlist';
    $path_to_file = $path_to_folder . '/ungerboeck.json';

    if (!file_exists($path_to_folder)) {
      $filespath = \Drupal::service('file_system')->mkdir($path_to_folder);
    }

    $search_url = Helpers::trim_slash($module_config->get('url')) . '/' . date('m-d-Y') . '/null/null/' . $module_config->get('account_number');

    // Fetch the page
    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $search_url);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 1);
    //curl_setopt($curl_handle, CURLOPT_HEADER, 1);

    $buffer = curl_exec($curl_handle);
    curl_close($curl_handle);

    if (strlen($buffer) > 0) {
      $myfile = fopen($path_to_file, 'w') or die('Unable to open file');
      fwrite($myfile, $buffer);
      fclose($myfile);
    }
  }

}

