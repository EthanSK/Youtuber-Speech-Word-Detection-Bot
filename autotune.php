<?php
echo("<pre>");
echo 'start of autotune'. "\n";
require_once('functions.php');
//for this to work make sure there is the acapella in autotune/originalsongs/songname/wholesong
$accessId = '97d90175-84a2-421f-a01f-da294c2e97d2';
$ffmpeg = './ffmpeg';
 if (!isset($titleOfSong)) {
     $titleOfSong = 'Fireflies';//needed for all
 }
 if (!isset($channelTitle)) {
     $channelTitle = "Postpartum Depression";//needed for all
 }
//
$titleOfSong = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $titleOfSong);
$channelTitle = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $channelTitle);

$path = "./autoTune/originalSongs/$titleOfSong/wholeSong";
$fileNameWithExtension = scandir_only_wanted_files($path)[0];
$path = $path. "/".scandir_only_wanted_files($path)[0];

echo "path: $path";
make_if_no_dir('./autotune');
make_if_no_dir('./autotune/originalSongs');
make_if_no_dir("./autotune/originalSongs/$titleOfSong");
make_if_no_dir("./autotune/originalSongs/$titleOfSong/wholeSong");
make_if_no_dir("./autotune/originalSongs/$titleOfSong/songLyrics");
//---------------------------------------------------------------------------------------------

//we don't even need to be ffmpegging these, we need to use the array with all the word based timestamps, then get the average pitch between the start and end times of this midi pitch array, then change the actual youtuber saying the word pitch but dont replace, put it in a new file
//what happens if we cant find the word? well what we should be doing is getting the original lyrcis as an array, then loop through, keeping track of how many times each lyric has been looped through. Then if there is more than one occurrence of the same lyric, we can differentiate by number of times it has occured ie its position so that we do not confuse pitches. Actually, we should be doing the pitch shifting as we loop through the original lyrics array. that way if the word is not found or is not equal to the folder lyric, in the word timestamps then just skip to the next one. make sure to have anew file with teh autotuned. no overwriting

//---------------------------------------------------------------------------------------------
//we need to query watson to get a full list of lyrics with timestamps for this audio file.

function get_song_word_timestamps($filePath)
{
    $username = 'febd5b22-d2e6-4127-a0b4-4f74aff66849';
    $password = 'RFdt5Im4PgHO';

    $file = fopen($filePath, 'r');
    $size = filesize($filePath);
    $filedata = fread($file, $size);
    $url = "https://stream.watsonplatform.net/speech-to-text/api/v1/recognize?timestamps=true&profanity_filter=false";

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
    curl_close($ch);
    $responseArray = json_decode($executed, true);

    return $responseArray;
}

echo 'path dir name '. dirname($path);

function make_path_webm($pathOriginal)
{
    $ffmpeg = './ffmpeg';

    $pathDirName= dirname($pathOriginal);
    $info = pathinfo($pathOriginal);
    $fileName = basename($pathOriginal, '.'.$info['extension']);
    unlink("$pathDirName/$fileName.webm");
    echo shell_exec("$ffmpeg  -i $pathOriginal -y $pathDirName/$fileName.webm 2>&1");
    echo "new webm path: $pathDirName/$fileName.webm";
    return "$pathDirName/$fileName.webm";
}

function download_individual_lyrics($arrayOfLyrics)
{
    global $path;
    global $titleOfSong;
    echo shell_exec("rm -R ./autotune/originalSongs/$titleOfSong/songLyrics");
    make_if_no_dir("./autotune/originalSongs/$titleOfSong/songLyrics");

    foreach ($arrayOfLyrics as $key => $valueArrayWithLyricData) {
        $startTime = $valueArrayWithLyricData[1];
        $endTime = $valueArrayWithLyricData[2];

        $name= $key."-".$valueArrayWithLyricData[0];
        echo shell_exec("./ffmpeg  -i $path -y -ss $startTime -to $endTime ./autotune/originalSongs/$titleOfSong/songLyrics/$name.aiff 2>&1");
    }
}

$newWebmPath = make_path_webm($path);
echo("<pre>");
echo "  newWebmPath \n";
print_r($newWebmPath);
echo("</pre>");

//if there is a markers.txt file with the timestamps, then we use that instead of watson, or at least as much of it fills the full song. make sure that the acapella is aligned with the clip we get the markers from in this case.

if (file_exists("./autoTune/originalSongs/$titleOfSong/wholeSong/markers.txt")) {
    $markers = file("./autoTune/originalSongs/$titleOfSong/wholeSong/markers.txt", FILE_IGNORE_NEW_LINES);
    $singleArrayWordTimestamps = array();
    foreach ($markers as $key => $value) {
        $lyric = strtolower(explode(" ", $value)[1]);
        if ($lyric=='-') {
            continue;
        }
        //the format is H:m:s:frame
        $startTimeMinutes = explode(":", $value)[1];
        $startTimeSeconds = explode(":", $value)[2];
        $startTimeCentiSeconds = ((explode(" ", explode(":", $value)[3])[0])/23.98);
        $startTime = $startTimeMinutes*60 + $startTimeSeconds + $startTimeCentiSeconds;

        $endTimeMinutes = explode(":", $markers[$key+1])[1];
        $endTimeSeconds = explode(":", $markers[$key+1])[2];
        $endTimeCentiSeconds = ((explode(" ", explode(":", $markers[$key+1])[3])[0])/23.98);
        $endTime = $endTimeMinutes*60 + $endTimeSeconds + $endTimeCentiSeconds;

        array_push($singleArrayWordTimestamps, array($lyric, $startTime, $endTime));
    }
} else {
    $wordTimestamps = get_song_word_timestamps($newWebmPath);
    $singleArrayWordTimestamps = array();
    foreach ($wordTimestamps['results'] as $keyOuter => $valueOuter) {
        foreach ($valueOuter['alternatives'][0]['timestamps'] as $keyInner => $valueInner) {
            array_push($singleArrayWordTimestamps, $valueInner);
        }
    }
}

//why did we call it download? its not downloading anything dumbass

echo("<pre>");
echo " word timestamps  \n";
print_r($singleArrayWordTimestamps);
echo("</pre>");

download_individual_lyrics($singleArrayWordTimestamps);


//---------------------------------------------------------------------------------------------
//get audio midi pitches
//changed it so now it calls this for each and every lyric, so this function call will be  after the watson stuff
function get_midi_parts($pathToLyric)
{
    //     $ch = curl_init ( 'https://api.sonicapi.com/file/upload?access_id=' . $accessId );
    // curl_setopt_array ( $ch, array (
    //         CURLOPT_POST => true,
    //         CURLOPT_POSTFIELDS => array (
    //                 'file'=>new CURLFile('shortaudio.mp3')
    //         ),
    //         CURLOPT_RETURNTRANSFER=>true
    // ) );
    // $output=curl_exec($ch);
    // curl_close($ch);
    global $accessId;
    shell_exec("curl https://api.sonicapi.com/file/upload?access_id=$accessId -F file=@$pathToLyric -o fileId.xml 2>&1");

    $xmlWithFileId = (array)simplexml_load_file('fileId.xml');
    //
    // echo ("<pre>");
    // echo " xml with file id\n";
    // print_r($xmlWithFileId);
    // echo ("</pre>");
    $fileXMLobject =  (array)$xmlWithFileId['file'];

    $fileId = explode("file_id=", $fileXMLobject['@attributes']['href'])[1];


    $taskUrl = 'analyze/melody';
    $parameters = array();
    $parameters['access_id'] = $accessId;
    $parameters['format'] = 'json';

    $parameters['input_file'] = $fileId;
    $parameters['detailed_result'] = 'false';

    // important: the calls require the CURL extension for PHP
    $ch = curl_init('https://api.sonicAPI.com/' . $taskUrl);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
    // you can remove the following line when using http instead of https, or
    // you point curl to the CA certificate
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $httpResponse = curl_exec($ch);
    $infos = curl_getinfo($ch);
    curl_close($ch);

    $responseMidiPitches = json_decode($httpResponse, true);

    if ($infos['http_code'] == 200) {
        //echo "Task succeeded, analysis result:<br />" ;
    } else {
        $errorMessages = array_map(function ($error) {
            return $error->message;
        }, $responseMidiPitches->errors);

        echo 'Task failed, reason: ' . implode('; ', $errorMessages);
    }

    return $responseMidiPitches;
}
//---------------------------------------------------------------------------------------------
function autotune_words($midiPitch, $wordPath)
{
    global $accessId;
    shell_exec("curl https://api.sonicapi.com/file/upload?access_id=$accessId -F file=@$wordPath -o fileId.xml 2>&1");

    $xmlWithFileId = (array)simplexml_load_file('fileId.xml');
    //
    // echo ("<pre>");
    // echo " xml with file id\n";
    // print_r($xmlWithFileId);
    // echo ("</pre>");
    $fileXMLobject =  (array)$xmlWithFileId['file'];

    $fileId = explode("file_id=", $fileXMLobject['@attributes']['href'])[1];

    $taskUrl = 'process/elastiqueTune';
    $parameters = array();
    $parameters['access_id'] = $accessId;
    $parameters['format'] = 'wav';

    $parameters['input_file'] = $fileId;
    $parameters['pitch_semitones'] = '0';
    $parameters['tempo_factor'] = '1';
    $parameters['formant_semitones'] = '0';
    $parameters['pitchcorrection_percent'] = '100';
    $parameters['pitchdrift_percent'] = '0';
    $parameters['midi_pitches'] = "$midiPitch-$midiPitch-$midiPitch-$midiPitch-$midiPitch-$midiPitch-$midiPitch-$midiPitch-$midiPitch-$midiPitch-$midiPitch-$midiPitch";

    // important: the calls require the CURL extension for PHP
    $ch = curl_init('https://api.sonicAPI.com/' . $taskUrl);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
    // you can remove the following line when using http instead of https, or
    // you point curl to the CA certificate
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $httpResponse = curl_exec($ch);
    $infos = curl_getinfo($ch);
    curl_close($ch);

    if ($infos['http_code'] == 200) {
        //file_put_contents('processing_result.mp3', $httpResponse);
        //echo "Task succeeded, file written to processing_result.mp3";
        return $httpResponse;
    } else {
        $responseDom = new DOMDocument();
        $responseDom->loadXML($httpResponse);
        $errorMessages = array();

        foreach ($responseDom->getElementsByTagName('errors')->item(0)->childNodes as $error) {
            $errorMessages[] = $error->attributes->getNamedItem('message')->nodeValue;
        }

        echo 'Task failed, reason: ' . implode('; ', $errorMessages);
    }
}

//---------------------------------------------------------------------------------------------
//now we need to get the lyric midi pitches and change the youtuber word
$trimmedFinalVideoDir = scandir_only_wanted_files("./downloadedVideos/$channelTitle/$titleOfSong/AudioOnlyOrderedTrimmedFinalVideo");
sort($trimmedFinalVideoDir, 1);

echo("<pre>");
echo "  trimmedFinalVideoDir \n";
print_r($trimmedFinalVideoDir);
echo("</pre>");

$arrayOfLyricsWithAveragePitch = array();



foreach ($trimmedFinalVideoDir as $key => $valueLyric) {
    //if the lyric can be found in the above watson call of the og song, then we can continue, otherwise skip to the next word.
    //shit, i just realised if the word is found but it's wrong then it will conut as not being found...ugh...
    //I genuinely think we need to do the method of just counting how many of a word we've found  and go down this list.
    echo shell_exec(" rm -R ./downloadedVideos/$channelTitle/$titleOfSong/AudioOnlyOrderedTrimmedFinalVideo/$valueLyric/autoTuned");

    echo shell_exec(" rm -R ./downloadedVideos/$channelTitle/$titleOfSong/orderedTrimmedFinalVideo/$valueLyric/autoTuned");
    echo("<pre>");
    echo "  valueLyric \n";
    print_r($valueLyric);
    echo("</pre>");

    $numberOfTimesThisLyricWasSaid = explode("_", $valueLyric)[2];

    $numberOftimesSongLyricSaidSoFarInLoop = 0;
    $pathOfSongLyricToUse= '';
    foreach ($singleArrayWordTimestamps as $keywordTimestamp => $valuewordTimestamp) {
        if ($valuewordTimestamp[0] == explode("_", $valueLyric)[1]) {
            $numberOftimesSongLyricSaidSoFarInLoop++;
            if ($numberOftimesSongLyricSaidSoFarInLoop == $numberOfTimesThisLyricWasSaid) {
                $pathOfSongLyricToUse = "./autotune/originalSongs/$titleOfSong/songLyrics/$keywordTimestamp-".$valuewordTimestamp[0].".aiff";
            }
        }
    }
    echo("<pre>");
    echo " pathOfSongLyricToUse:  \n";
    print_r($pathOfSongLyricToUse);
    echo("</pre>");

    if (empty($pathOfSongLyricToUse)) {
        continue;
    }

    //get average pitch for this time range

    $arrayOfMidiPartsForThisLyric =
 get_midi_parts($pathOfSongLyricToUse);

    if (empty($arrayOfMidiPartsForThisLyric['melody_result']['notes'])) {
        echo 'no notes detected so stretching';
        $dir = pathinfo($pathOfSongLyricToUse)['dirname'];
        $name = pathinfo($pathOfSongLyricToUse)['filename'];
        $pathnameStretched = $dir.'/'.$name."-stretched.aiff";
        echo shell_exec("$ffmpeg  -i $pathOfSongLyricToUse -y -filter:a 'atempo=0.5' $pathnameStretched 2>&1");
        //instead of skipping lets try and stretch it and see
        //continue;
        $arrayOfMidiPartsForThisLyric =
      get_midi_parts($pathnameStretched);
        if (empty($arrayOfMidiPartsForThisLyric['melody_result']['notes'])) {
            echo 'still empty so just continuing';
            continue;
        }
    }

    usort($arrayOfMidiPartsForThisLyric['melody_result']['notes'], function ($a, $b) {
        return $a['duration']*10000 - $b['duration']*10000;
    });
    $arrayOfMidiPartsForThisLyric['melody_result']['notes'] = array_reverse($arrayOfMidiPartsForThisLyric['melody_result']['notes']);


    //we can do this since we just sorted so the longest one with be the zeroth
    //sometimes it cannot detect, so in this case we have to ignore
    $midiPitchToUseForThisLyric = $arrayOfMidiPartsForThisLyric['melody_result']['notes'][0]['midi_pitch'];
    echo 'midipitch is '. $midiPitchToUseForThisLyric;
    $pathToAudioFilesToAutotune = scandir_only_wanted_files("./downloadedVideos/$channelTitle/$titleOfSong/AudioOnlyOrderedTrimmedFinalVideo/$valueLyric");

    foreach ($pathToAudioFilesToAutotune as $valueActualWordFile) {
        print_r(pathinfo("./downloadedVideos/$channelTitle/$titleOfSong/AudioOnlyOrderedTrimmedFinalVideo/$valueLyric/$valueActualWordFile"));
        if (pathinfo("./downloadedVideos/$channelTitle/$titleOfSong/AudioOnlyOrderedTrimmedFinalVideo/$valueLyric/$valueActualWordFile")['extension'] == 'aiff') {
            $autoTuneResponse = autotune_words($midiPitchToUseForThisLyric, "./downloadedVideos/$channelTitle/$titleOfSong/AudioOnlyOrderedTrimmedFinalVideo/$valueLyric/$valueActualWordFile");
            $nameOfNewFile = pathinfo("./downloadedVideos/$channelTitle/$titleOfSong/AudioOnlyOrderedTrimmedFinalVideo/$valueLyric/$valueActualWordFile")['filename'];

            make_if_no_dir("./downloadedVideos/$channelTitle/$titleOfSong/AudioOnlyOrderedTrimmedFinalVideo/$valueLyric/autoTuned");
            file_put_contents("./downloadedVideos/$channelTitle/$titleOfSong/AudioOnlyOrderedTrimmedFinalVideo/$valueLyric/autoTuned/$nameOfNewFile.wav", $autoTuneResponse);

            make_if_no_dir("./downloadedVideos/$channelTitle/$titleOfSong/orderedTrimmedFinalVideo/$valueLyric/autoTuned");
            echo shell_exec("$ffmpeg -i ./downloadedVideos/$channelTitle/$titleOfSong/orderedTrimmedFinalVideo/$valueLyric/$nameOfNewFile.mp4 -i ./downloadedVideos/$channelTitle/$titleOfSong/AudioOnlyOrderedTrimmedFinalVideo/$valueLyric/autoTuned/$nameOfNewFile.wav -c:v copy -map 0:v:0 -map 1:a:0 -shortest ./downloadedVideos/$channelTitle/$titleOfSong/orderedTrimmedFinalVideo/$valueLyric/autoTuned/$nameOfNewFile.mp4 2>&1");
        }
    }
}



//---------------------------------------------------------------------------------------------
echo 'end of autotune';
echo("</pre>");
