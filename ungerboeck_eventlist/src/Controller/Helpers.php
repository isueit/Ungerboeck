<?php
namespace Drupal\ungerboeck_eventlist\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ungerboeck_eventlist\Controller\Helpers;
use ZipArchive;

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

    $myfile = fopen($path_to_file, 'r') or die('Unable to open file!' . $path_to_file);
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
    $buffer = Helpers::curl_call($search_url);

    if (strlen($buffer) > 0) {
      $myfile = fopen($path_to_file, 'w') or die('Unable to open file' . $path_to_file);
      fwrite($myfile, $buffer);
      fclose($myfile);
    }
  }


  /*
   * Return the contents of the file that is storing the Qualtrics survey for Human Sciences
   */
  public static function hs_read_qualtrics_file() {
    $returnstr = '';
    $path_to_file = \Drupal::service('file_system')->realpath(file_default_scheme() . "://") . '/ungerboeck_eventlist/hs_qualtrics.json';

    if (!file_exists($path_to_file) || (date('z', time()) <> date('z', filemtime($path_to_file))) || filesize($path_to_file) == 0) {
      Helpers::hs_create_qualtrics_file();
    }

    $myfile = fopen($path_to_file, 'r') or die('Unable to open file!' . $path_to_file);
    $returnstr = fread($myfile, filesize($path_to_file));
    fclose($myfile);
    return $returnstr;
  }


  /**
   * Get the survey file from Qualtrics, and save it in the files folder
   */
  public static function hs_create_qualtrics_file() {
    $module_config = \Drupal::config('ungerboeck_eventlist.settings');
    if (empty($module_config->get('hs_qualtrics_token')) || empty($module_config->get('hs_qualtrics_surveyID'))) {
      return;
    }

    $path_to_folder = \Drupal::service('file_system')->realpath(file_default_scheme() . "://") . '/ungerboeck_eventlist';
    $path_to_file = $path_to_folder . '/hs_qualtrics.json';

    if (!file_exists($path_to_folder)) {
      $filespath = \Drupal::service('file_system')->mkdir($path_to_folder);
    }

    $progressStatus = 'inProgress';
    $baseURL = 'https://iastate.ca1.qualtrics.com/API/v3/surveys/' . $module_config->get('hs_qualtrics_surveyID') . '/export-responses/';
    $headers = array(
      'x-api-token: ' . $module_config->get('hs_qualtrics_token'), 
      'content-type: application/json',
    );

    $result = Helpers::curl_call($baseURL, $headers, '{"format":"json"}');

    $json = json_decode($result, TRUE);
    $progressID = $json['result']['progressId'];

    $count = 0;
    while ($progressStatus != "complete" && $progressStatus != 'failed' && $count++ < 50) {
      $result = Helpers::curl_call($baseURL . $progressID, $headers);
      $json = json_decode($result, TRUE);
      $progressStatus = $json['result']['status'];
    }

    if ($progressStatus == 'failed' || $count >= 50) {
      return;
    }

    $json = json_decode($result, TRUE);

    $result = Helpers::curl_call($baseURL . $json['result']['fileId'] . '/file', $headers);

    $tmpfile = tempnam($path_to_folder, 'hs_');

    file_put_contents($tmpfile, $result);
    $zip = new ZipArchive;
    $res = $zip->open($tmpfile);
    if ($res === TRUE) {
      $zipfile = $zip->getNameIndex(0);
      $zip->extractTo($path_to_folder, $zipfile);
      $zip->close();
      rename($path_to_folder . '/' . $zipfile, $path_to_file);
    }

    \Drupal::service('file_system')->unlink($tmpfile);
  }

  /**
   * Function to make a curl call. There are other options that can be added
   * using curl_setopt for debugging purposes. You'll need to do a search for
   * what's available.
   *
   * Return value is the results of making the curl call.
   */
  public static function curl_call($url, $headers = NULL, $postfields = NULL) {
    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL, $url);
    if (!empty($headers)) {
      curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
    }
    if (!empty($postfields)) {
      curl_setopt($ch,CURLOPT_POSTFIELDS, $postfields);
    }

    //So that curl_exec returns the contents of the cURL; rather than echoing it
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

    //execute post
    $result = curl_exec($ch);

    return $result;
  }

}

