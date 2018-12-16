<?php
echo ' ogetting auto gen subs ';
define('SRT_STATE_SUBNUMBER', 0);
define('SRT_STATE_TIME', 1);
define('SRT_STATE_TEXT', 2);
define('SRT_STATE_BLANK', 3);

foreach ($arrayOfChannelVideoIDs as $keyVideo => $valueVideoID) {
    echo 'key video'. $keyVideo;
    unlink("subtitlesAutoGen.srt");
    unlink("subtitlesAutoGen.en.vtt");
    shell_exec("$youtubedl --write-auto-sub --skip-download --sub-format vtt  https://www.youtube.com/watch?v=$valueVideoID -o subtitlesAutoGen");
    shell_exec("$ffmpeg -i subtitlesAutoGen.en.vtt -y subtitlesAutoGen.srt");


    $lines   = file('subtitlesAutoGen.srt');
    echo "\n lines: ";
    print_r($lines);

    $subs    = array();
    $state   = SRT_STATE_SUBNUMBER;
    $subNum  = 0;
    $subText = '';
    $subTime = '';


    //this is deprecated coz i think yt changed the way they do auto subs
    // foreach ($lines as $line) {
    //     switch ($state) {
    //     case SRT_STATE_SUBNUMBER:
    //         $subNum = trim($line);
    //         $state  = SRT_STATE_TIME;
    //         break;
    //
    //     case SRT_STATE_TIME:
    //         $subTime = trim($line);
    //         $state   = SRT_STATE_TEXT;
    //         break;
    //
    //     case SRT_STATE_TEXT:
    //         if (trim($line) == '') {
    //             $sub = new stdClass;
    //             $sub->number = $subNum;
    //             list($sub->startTime, $sub->stopTime) = explode(' --> ', $subTime);
    //             $sub->text   = $subText;
    //             $subText     = '';
    //             $state       = SRT_STATE_SUBNUMBER;
    //
    //             $subs[]      = $sub;
    //         } else {
    //             $subText .= $line;
    //         }
    //       }
    // }



    $linesAlreadyUsed = array();
    $subs = array();
    $lastNumberUsed = 1;
    foreach ($lines as $key => $valueLine) {
        if ($key % 5 != 0) {//only get multiples of 5
            continue;
        }
        //echo "\nlines already used ";
        //print_r($linesAlreadyUsed);
        //the index of the  number occurs every 5 elemnts so 0, 5, 10, etc
        //the keys of the sub elements are number, startTime, stopTime, text
        //the time duration is always on the line after the numbers

        //get time and number for this section

        $subTime = $lines[$key + 1];
        echo " sub time" . $subTime;
        print_r(explode(' --> ', $subTime));
        $startTime = explode(' --> ', $subTime)[0];
        $stopTime = explode(' --> ', $subTime)[1];
        $startTime = cleanSpaceAndNewlineText($startTime);
        $stopTime = cleanSpaceAndNewlineText($stopTime);

        //filter out text in brackets and get text we wants

        $text = "";
        //first filter
        for ($i = 0; $i < 3; $i++) {//there are always 3 lines available for text as the first 2 of the 5 are for the number and duration
            $lineText = $lines[$key + $i + 2];
            echo "   line text $lineText  ";
            if (stripos($lineText, "[") === false && !in_array(cleanSpaceAndNewlineText($lineText), $linesAlreadyUsed)) {
                //[ was not found so can use this line
                //also line has not ever been used before for this vid
                $text .= $lineText . " ";
                array_push($linesAlreadyUsed, cleanSpaceAndNewlineText($lineText));
            }
        }
        $text = cleanSpaceAndNewlineText($text);
        if ($text == " " || $text == "") {
            continue;
        }
        $lastNumberUsed++;
        $number = $lastNumberUsed;
        array_push($subs, array('number' => $number,'stopTime' => $stopTime, 'startTime' => $startTime, 'text' => $text));
    }



    echo "\nsubs : ";
    print_r($subs);
    $subs = json_decode(json_encode((array)$subs), true);
    //first add duration element
    foreach ($subs as $key => $valueSub) {
        $startTimeProperlyFormatedWithoutDecimals = explode(",", $valueSub['startTime'])[0];
        $stopTimeProperlyFormatedWithoutDecimals = explode(",", $valueSub['stopTime'])[0];

        $startTimeInSeconds = strtotime($startTimeProperlyFormatedWithoutDecimals) - strtotime('TODAY');
        $stopTimeInSeconds =  strtotime($stopTimeProperlyFormatedWithoutDecimals) - strtotime('TODAY') ;

        $startTimeInSeconds = $startTimeInSeconds.'.'. explode(",", $valueSub['startTime'])[1];
        $stopTimeInSeconds = $stopTimeInSeconds.'.'.explode(",", $valueSub['stopTime'])[1];

        $subs[$key]['startTimeSeconds'] = $startTimeInSeconds;
        $subs[$key]['stopTimeSeconds'] = $stopTimeInSeconds;
    }
    foreach ($subs as $key => $valueSub) {
        if (isset($subs[$key+1])) {
            $subs[$key]['dur'] =  $subs[$key+1]['startTimeSeconds'] - $subs[$key]['startTimeSeconds'];
        } else {
            $subs[$key]['dur'] =  $subs[$key]['stopTimeSeconds'] - $subs[$key]['startTimeSeconds'];
        }
    }
    $newFormattedArray = array();

    foreach ($subs as $key => $valueSub) {
        array_push($newFormattedArray, array(
    '@attributes' => array("start" => $valueSub['startTimeSeconds'], 'dur' => $valueSub['dur']),
    '0' => $valueSub['text']
  ));
    }


    $arrayWithYoutubeTranscript[$valueVideoID] = $newFormattedArray;
}
