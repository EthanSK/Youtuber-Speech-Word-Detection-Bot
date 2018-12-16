<?php
echo("<pre>");

// $howAreWeGettingTheVideos = 3;
// $numberOfVideosToGetCaptionsFrom = 10;
// $query = 'weekly address';
// $channelToGetVidsFrom = 'UCYxRlFDqcWM4y7FfpiAN3KQ';
//$channelTitle = 'not_set_yet';

/**
 * Library Requirements
 *
 * 1. Install composer (https://getcomposer.org)
 * 2. On the command line, change to this directory (api-samples/php)
 * 3. Require the google/apiclient library
 *    $ composer require google/apiclient:~2.0
 */
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
}

require_once __DIR__ . '/vendor/autoload.php';
session_start();

/*
 * You can acquire an OAuth 2.0 client ID and client secret from the
 * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
 * For more information about using OAuth 2.0 to access Google APIs, please see:
 * <https://developers.google.com/youtube/v3/guides/authentication>
 * Please ensure that you have enabled the YouTube Data API for your project.
 */
$OAUTH2_CLIENT_ID = 'you have';
$OAUTH2_CLIENT_SECRET = 'now encountered';
$refreshToken = 'god himself';

$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$client->setScopes('https://www.googleapis.com/auth/youtube');
$redirect = filter_var(
    'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
  FILTER_SANITIZE_URL
);
$client->setRedirectUri($redirect);
$client->refreshToken($refreshToken);


// Define an object that will be used to make all API requests.
$youtube = new Google_Service_YouTube($client);

// Check if an auth token exists for the required scopes
$tokenSessionKey = 'token-' . $client->prepareScopes();
if (isset($_GET['code'])) {
    if (strval($_SESSION['state']) !== strval($_GET['state'])) {
        die('The session state did not match.');
    }

    $client->authenticate($_GET['code']);
    $_SESSION[$tokenSessionKey] = $client->getAccessToken();
    header('Location: ' . $redirect);
}

if (isset($_SESSION[$tokenSessionKey])) {
    $client->setAccessToken($_SESSION[$tokenSessionKey]);
}

// Check to ensure that the access token was successfully acquired.
if ($client->getAccessToken()) {
    try {
        function ISO8601ToSeconds($ISO8601)
        {
            $interval = new \DateInterval($ISO8601);

            return ($interval->d * 24 * 60 * 60) +
            ($interval->h * 60 * 60) +
            ($interval->i * 60) +
            $interval->s;
        }
        $arrayOfChannelVideoIDs = array();

        if ($howAreWeGettingTheVideos ==1) {
            //---------------------------------------------------------------------------------------------
            //get videos
            // Call the channels.list method to retrieve information about the
            // currently authenticated user's channel.
            $channelsResponse = $youtube->channels->listChannels('contentDetails', array(
      'id' => $channelToGetVidsFrom,
    ));





            $htmlBody = '';
            $videosGottenSoFar = 0;
            $videosRemaining = $numberOfVideosToGetCaptionsFrom;
            foreach ($channelsResponse['items'] as $channel) {
                // Extract the unique playlist ID that identifies the list of videos
                // uploaded to the channel, and then call the playlistItems.list method
                // to retrieve that list.
                $uploadsListId = $channel['contentDetails']['relatedPlaylists']['uploads'];
                $playlistItemsResponse = $youtube->playlistItems->listPlaylistItems('snippet,contentDetails', array(
        'playlistId' => $uploadsListId,
        'maxResults' => min(max($videosRemaining, 0), 50)
      ));

                echo("<pre>");
                echo "   \n";
                // print_r($playlistItemsResponse);
                echo("</pre>");

                $videosGottenSoFar += min(max($videosRemaining, 0), 50);
                $videosRemaining = $numberOfVideosToGetCaptionsFrom - $videosGottenSoFar;
                echo 'videos remaining '.$videosRemaining;
                $loopsRemaining = ceil($videosRemaining /50);
                echo ' loops remaining '.$loopsRemaining;

                $channelTitle = $playlistItemsResponse['items'][0]['snippet']['channelTitle'];
                $channelTitle = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $channelTitle);

                foreach ($playlistItemsResponse['items'] as $key => $value) {
                    $response = $youtube->videos->listVideos(
            'contentDetails',
            array('id' => $value['snippet']['resourceId']['videoId'])
        );
                    $durationInSeconds = $response['items'][0]["contentDetails"]['duration'];
                    $timeInSeconds = ISO8601ToSeconds($durationInSeconds). ' ';
                    if ($timeInSeconds < $maxLengthOfVideoAllowed) {
                        array_push($arrayOfChannelVideoIDs, $value['snippet']['resourceId']['videoId']);
                    }
                }


                $a = 0;
                while ($a < $loopsRemaining) {
                    $playlistItemsResponse = $youtube->playlistItems->listPlaylistItems('snippet', array(
          'playlistId' => $uploadsListId,
          'maxResults' => min(max($videosRemaining, 0), 50),
          'pageToken' => $playlistItemsResponse['nextPageToken'],
        ));
                    $videosGottenSoFar += min(max($videosRemaining, 0), 50);
                    $videosRemaining = $numberOfVideosToGetCaptionsFrom -$videosGottenSoFar;
                    foreach ($playlistItemsResponse['items'] as $key => $value) {
                        $response = $youtube->videos->listVideos(
              'contentDetails',
              array('id' => $value['snippet']['resourceId']['videoId'])
          );
                        $durationInSeconds = $response['items'][0]["contentDetails"]['duration'];
                        $timeInSeconds = ISO8601ToSeconds($durationInSeconds). ' ';
                        if ($timeInSeconds < $maxLengthOfVideoAllowed) {
                            array_push($arrayOfChannelVideoIDs, $value['snippet']['resourceId']['videoId']);
                        }
                    }


                    $a++;
                }


                echo("<pre>");
                echo " channel video ids  \n";
                print_r($arrayOfChannelVideoIDs);
                echo("</pre>");
            }
            $htmlBody .= "<h3>Videos in list $uploadsListId</h3><ul>";
            foreach ($playlistItemsResponse['items'] as $playlistItem) {
                $htmlBody .= sprintf(
            '<li>%s (%s)</li>',
            $playlistItem['snippet']['title'],
          $playlistItem['snippet']['resourceId']['videoId']
        );
            }
            $htmlBody .= '</ul>';
        }
        //---------------------------------------------------------------------------------------------
        if ($howAreWeGettingTheVideos ==3) {
            $arrayOfTitles = array();
            echo 'getting the videos through a query';

            function searchListByKeyword($service, $part, $params)
            {
                $params = array_filter($params);
                $response = $service->search->listSearch(
            $part,
            $params
        );

                echo("<pre>");
                echo "   \n";
                //print_r($response);
                echo("</pre>");
                return $response;
            }
            $videosGottenSoFar = 0;
            $videosRemaining = $numberOfVideosToGetCaptionsFrom;

            $playlistItemsResponse = searchListByKeyword(
        $youtube,
        'snippet',
        array('maxResults' => min(max($videosRemaining, 0), 50), 'q' => $query, 'type'=> 'video', 'channelId' => $channelToGetVidsFrom)
    );


            $videosGottenSoFar += min(max($videosRemaining, 0), 50);
            $videosRemaining = $numberOfVideosToGetCaptionsFrom - $videosGottenSoFar;
            $loopsRemaining = ceil($videosRemaining /50);

            foreach ($playlistItemsResponse['items'] as $key => $value) {
                array_push($arrayOfTitles, $value['snippet']['title']);
                echo 'getting info on '.$value['id']['videoId'];
                $response = $youtube->videos->listVideos(
          'contentDetails',
          array('id' => $value['id']['videoId'])
      );
                $durationInSeconds = $response['items'][0]["contentDetails"]['duration'];
                $timeInSeconds = ISO8601ToSeconds($durationInSeconds). ' ';
                if ($timeInSeconds < $maxLengthOfVideoAllowed) {
                    if (empty($value['id']['videoId'])) {
                        continue;
                    }
                    if (!empty($schmoyohoLimitationsMustGetOneTypeOfVideo)) {
                        foreach ($schmoyohoLimitationsMustGetOneTypeOfVideo as $valueOfTitleRequirement) {
                            if ($valueOfTitleRequirement == $query) {
                                if (stripos($value['snippet']['title'], $valueOfTitleRequirement) !== false) {
                                    echo '   title of this video that got past scho lims is '. $value['snippet']['title']. "\n";
                                    array_push($arrayOfChannelVideoIDs, $value['id']['videoId']);
                                } else {
                                    echo 'there was no schmoyoho requirement found in the title '. "\n";
                                }
                            }
                        }
                    } else {
                        array_push($arrayOfChannelVideoIDs, $value['id']['videoId']);
                    }
                }
            }
            echo 'finished loop';



            $a = 0;
            while ($a < $loopsRemaining) {
                $playlistItemsResponse = searchListByKeyword(
                  $youtube,
                  'snippet',
                  array('maxResults' => min(max($videosRemaining, 0), 50), 'q' => $query, 'type'=> 'video','channelId' => $channelToGetVidsFrom,'pageToken' => $playlistItemsResponse['nextPageToken'],
                    )
              );
                $videosGottenSoFar += min(max($videosRemaining, 0), 50);
                $videosRemaining = $numberOfVideosToGetCaptionsFrom -$videosGottenSoFar;
                foreach ($playlistItemsResponse['items'] as $key => $value) {
                    array_push($arrayOfTitles, $value['snippet']['title']);

                    echo 'getting info on '.$value['id']['videoId'];

                    $response = $youtube->videos->listVideos(
                          'contentDetails',
                          array('id' => $value['id']['videoId'])
                      );
                    $durationInSeconds = $response['items'][0]["contentDetails"]['duration'];
                    $timeInSeconds = ISO8601ToSeconds($durationInSeconds). ' ';
                    if ($timeInSeconds < $maxLengthOfVideoAllowed) {
                        if (empty($value['id']['videoId'])) {
                            continue;
                        }


                        if (!empty($schmoyohoLimitationsMustGetOneTypeOfVideo)) {
                            foreach ($schmoyohoLimitationsMustGetOneTypeOfVideo as $valueOfTitleRequirement) {
                                if ($valueOfTitleRequirement == $query) {
                                    if (stripos($value['snippet']['title'], $valueOfTitleRequirement) !== false) {
                                        echo 'the title of this video that got past scho lims is '. $value['snippet']['title']. "\n";
                                        array_push($arrayOfChannelVideoIDs, $value['id']['videoId']);
                                    } else {
                                        echo 'from while there was no schmoyoho requirement found in the title '. "\n";
                                    }
                                }
                            }
                        } else {
                            array_push($arrayOfChannelVideoIDs, $value['id']['videoId']);
                        }
                    }
                }

                $a++;
            }
            echo("<pre>");
            echo " arrayOfChannelVideoIDs  \n";
            print_r($arrayOfChannelVideoIDs);
            echo("</pre>");

            echo("<pre>");
            echo " array of titles  \n";
            print_r($arrayOfTitles);
            echo("</pre>");
        }



        //---------------------------------------------------------------------------------------------
    } catch (Google_Service_Exception $e) {
        $htmlBody = sprintf(
        '<p>A service error occurred: <code>%s</code></p>',
      htmlspecialchars($e->getMessage())
    );
    } catch (Google_Exception $e) {
        $htmlBody = sprintf(
        '<p>An client error occurred: <code>%s</code></p>',
      htmlspecialchars($e->getMessage())
    );
    }

    $_SESSION[$tokenSessionKey] = $client->getAccessToken();
} elseif ($OAUTH2_CLIENT_ID == 'REPLACE_ME') {
    $htmlBody = <<<END
  <h3>Client Credentials Required</h3>
  <p>
    You need to set <code>\$OAUTH2_CLIENT_ID</code> and
    <code>\$OAUTH2_CLIENT_ID</code> before proceeding.
  <p>
END;
} else {
    $state = mt_rand();
    $client->setState($state);
    $_SESSION['state'] = $state;

    $authUrl = $client->createAuthUrl();
    $htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
END;
}

echo("</pre>");
?>

<!doctype html>
<html>
  <head>
    <title>My Uploads</title>
  </head>
  <body>
    <?=$htmlBody?>

  </body>
</html>
