<?php
require_once('functions.php');

function scandir_only_wanted_files($path)
{
    $dirArray = scandir($path);
    foreach ($dirArray as $key => $value) {
        if ($value == '.'|| $value == '..'|| $value == '.DS_Store') {
            unset($dirArray[$key]);
        }
    }
    $dirArray = array_values($dirArray);
    // echo ("<pre>");
    // echo " processed dirArray  \n";
    // print_r($dirArray);
    // echo ("</pre>");
    return $dirArray;
}

function cleanSpaceAndNewlineText($text)
{
    return trim(preg_replace('/\s\s+/', ' ', $text));
}

function array_count_values_of($value, $array)
{
    $counts = array_count_values($array);
    return $counts[$value];
}

  function make_if_no_dir($dirPath)
  {
      if (!is_dir($dirPath)) {
          mkdir($dirPath);
      }
  }

  function dirToArray($dir)
  {
      $result = array();
      $cdir = scandir($dir);

      foreach ($cdir as $key => $value) {
          if (!in_array($value, array(".","..", ".DS_Store"))) {
              if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                  $result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value);
              } else {
                  $result[] = $value;
              }
          }
      }
      return $result;
  }
  
  function printArray($array, $title)
  {
      echo("<pre>");
      echo $title . ":\n";
      print_r($array);
      echo("</pre>");
  }

  function prettyPrint($text)
  {
      echo("<pre>");
      echo "\n" . $text . "\n";
      echo("</pre>");
  }
  function use_watson($filePath, $keywords)
  {
      echo "\nuse watson called";

      global $usernameWatson, $passwordWatson;
      require_once 'vendor/autoload.php';

      $file = fopen($filePath, 'r');
      $size = filesize($filePath);
      $filedata = fread($file, $size);
      $url = "https://stream.watsonplatform.net/speech-to-text/api/v1/recognize?keywords=$keywords&keywords_threshold=0.0&profanity_filter=false";




//       $client = new GuzzleHttp\Client([
//     'base_uri' => 'https://stream.watsonplatform.net/'
      // ]);
//
      // $audio = $file;
      // $resp = $client->request('POST', "speech-to-text/api/v1/recognize?keywords=$keywords&keywords_threshold=0.0&profanity_filter=false", [
//     'auth' => [$usernameWatson, $passwordWatson],
//     'headers' => [
//         'Content-Type' => 'audio/webm',
//     ],
//     'body' => $audio
      // ]);
//
      // echo $resp->getBody();




      $headers = array(    "Content-Type: audio/webm",
                         "Transfer-Encoding: chunked");
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $filedata);
      curl_setopt($ch, CURLOPT_INFILE, $file);
      curl_setopt($ch, CURLOPT_INFILESIZE, $size);
      curl_setopt($ch, CURLOPT_VERBOSE, true);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      $executed = curl_exec($ch);
      echo "\nexectuted result" . $executed;
      curl_close($ch);
      $responseArray = json_decode($executed, true);
      echo("<pre>");
      echo "  response array with lyric $keywords\n";
      print_r($responseArray);
      echo("</pre>");
      return $responseArray;
  }
