<?php
echo("<pre>");
echo "start of bot video creation \n";
require_once('functions.php');

$shouldKeepAsMuchSlackAsPossible = 1;
if (!isset($titleOfSong)) {
    $titleOfSong = 'Fireflies';//needed for all
}
if (!isset($channelTitle)) {
    $channelTitle = "Markiplier";//needed for all
}
$numberOfAlternativesToGenerate = 1;
$framerate = 60;
if (!isset($amountToAddToEndAndSubtractFromStart)) {
    $amountToAddToEndAndSubtractFromStart = 0.3;//needed for all
}
ini_set("auto_detect_line_endings", true);
//error_reporting(E_ALL & ~E_NOTICE);

$titleOfSong = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $titleOfSong);
$channelTitle = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $channelTitle);
//$text=iconv("UTF-8", "ISO-8859-1//IGNORE", $text)


//make sure the text file is called markers btw
$markers = file("./botVideoCreation/$titleOfSong/markers.txt", FILE_IGNORE_NEW_LINES);
foreach ($markers as $key => $value) {
    $markers[$key] = strtolower($value);
}
echo("<pre>");
echo "  markers \n";
print_r($markers);
echo("</pre>");
//---------------------------------------------------------------------------------------------
//check to make sure that all the files are in the final bot video creation folder
function check_all_files_are_there($indexToCheck)
{
    global $channelTitle, $titleOfSong, $amountToAddToEndAndSubtractFromStart, $framerate;

    $arrayOfFinalFiles = scandir_only_wanted_files("./downloadedVideos/$channelTitle/$titleOfSong/botVideoCreation/$indexToCheck");
    asort($arrayOfFinalFiles, 1);
    $arrayOfFinalFiles = array_values($arrayOfFinalFiles);
    echo("<pre>");
    echo "  arrayOfFinalFiles \n";
    print_r($arrayOfFinalFiles);
    echo("</pre>");

    $arrayOfFinalFilesNotThere = array();
    foreach ($arrayOfFinalFiles as $key => $value) {
        if (explode('_', $value)[0] -1 != explode('_', $arrayOfFinalFiles[$key-1])[0]) {
            array_push($arrayOfFinalFilesNotThere, $value);
        }
    }
    asort($arrayOfFinalFilesNotThere, 1);
    $arrayOfFinalFilesNotThere = array_values($arrayOfFinalFilesNotThere);

    echo("<pre>");
    echo " files not there + 1  \n";
    print_r($arrayOfFinalFilesNotThere);
    echo("</pre>");
    exit();
}
//check_all_files_are_there(0);
echo shell_exec(" rm -R ./downloadedVideos/$channelTitle/$titleOfSong/botVideoCreation");
make_if_no_dir("./botVideoCreation/$titleOfSong");

make_if_no_dir("./downloadedVideos/$channelTitle/$titleOfSong/botVideoCreation");
//---------------------------------------------------------------------------------------------
//migrate marker lyrics to new marker txt
function migrate_marker_lyrics($fromArray, $toFile)
{
    $targetArray = file($toFile, FILE_IGNORE_NEW_LINES);
    echo("<pre>");
    echo "  target array before \n";
    print_r($targetArray);
    echo("</pre>");
    foreach ($fromArray as $key => $value) {
        $wordFromOriginal = explode(' ', $value)[1];
        $targetArray[$key] = explode(' ', $targetArray[$key])[0]. ' '. $wordFromOriginal;
    }
    echo("<pre>");
    echo "  target array after \n";
    print_r($targetArray);
    echo("</pre>");
    file_put_contents($toFile, '');
    foreach ($targetArray as $key => $value) {
        file_put_contents($toFile, "$value\n", FILE_APPEND);
    }
    exit();
}
//migrate_marker_lyrics($markers, "./botVideoCreation/$titleOfSong/markers60.txt");
//---------------------------------------------------------------------------------------------
//confirm that all the words are there and spelled right
$lyricsInFolder = scandir_only_wanted_files("./downloadedVideos/$channelTitle/$titleOfSong/orderedTrimmedFinalVideo");
sort($lyricsInFolder, 1);

$markersWithoutGaps = array();
foreach ($markers as $key => $value) {
    if (stripos($value, '-') === false) {
        array_push($markersWithoutGaps, strtolower($value));
    }
}
// echo ("<pre>");
// echo "  markersWithoutGaps \n";
// print_r($markersWithoutGaps);
// echo ("</pre>");
//
// echo("<pre>");
// echo "  lyricsInFolder \n";
// print_r($lyricsInFolder);
// echo("</pre>");
if (!isset($songLyrics)) {
    $songLyrics = file('songLyrics.txt', FILE_IGNORE_NEW_LINES);
}
$explodedSongLyrics = array();
foreach ($songLyrics as $key => $value) {
    foreach (explode(" ", $value) as $keyExploded => $valueExploded) {
        if (!empty($valueExploded)) {
            $valueExploded = str_replace("'", '', $valueExploded);
            $valueExploded = preg_replace("/[^A-Za-z0-9 ]/", "", $valueExploded);
            array_push($explodedSongLyrics, strtolower($valueExploded));
        }
    }
}



$arrayToConfirm = array();
foreach ($explodedSongLyrics as $key => $value) {
    $arrayToConfirm[$key]= array();
    array_push($arrayToConfirm[$key], $value);
}

foreach ($markersWithoutGaps as $key => $value) {
    array_push($arrayToConfirm[$key], $value);
}

foreach ($lyricsInFolder as $key => $value) {
    array_push($arrayToConfirm[$key], $value);
}

// echo("<pre>");
// echo "   array to confirm\n";
// print_r($arrayToConfirm);
// echo("</pre>");
// $shouldExit = 0;

foreach ($arrayToConfirm as $key => $value) {
    if ($key < count($markersWithoutGaps)) {
        if (explode(" ", $value[1])[1] != $value[0]) {
            echo "  This was not found markerLyrics:  ";
            echo $value[1];
            echo ", should be $value[0]\n";
            $shouldExit = true;
        }
        if (explode("_", $value[2])[1] != $value[0]) {
            echo "This was not found folderLyrics: ";
            echo $value[2];
            echo ", should be $value[0]\n";
            $shouldExit = true;
        }
    }
}
if ($shouldExit) {
    exit('  there was an error in the markers txt so exiting  ');
}
//---------------------------------------------------------------------------------------------
//remove array elements with no files in the endpoint
$orderedTrimmedFinalVideo = dirToArray("./downloadedVideos/$channelTitle/$titleOfSong/orderedTrimmedFinalVideo");
$pathToOrderedTrimmedFolder = "./downloadedVideos/$channelTitle/$titleOfSong/orderedTrimmedFinalVideo";
ksort($orderedTrimmedFinalVideo, 1);

foreach ($orderedTrimmedFinalVideo as $keyLyric => $valueLyricArray) {
    foreach ($valueLyricArray as $keySubword => $valueSubwordArray) {
        if (is_array($valueSubwordArray)) {
            $foundSimilarSubword = false;
            foreach ($valueSubwordArray as $keySimilarSubword => $valueSimilarSubWordArray) {
                if (empty($valueSimilarSubWordArray)) {
                    unset($orderedTrimmedFinalVideo[$keyLyric][$keySubword][$keySimilarSubword]);
                } else {
                    $foundSimilarSubword = true;
                }
            }
            if (!$foundSimilarSubword) {
                unset($orderedTrimmedFinalVideo[$keyLyric][$keySubword]);
            }
        } else {
            if (stripos($valueSubwordArray, 'added') !== false) {
                unset($orderedTrimmedFinalVideo[$keyLyric][$keySubword]);
            }
        }
    }
    array_unshift($orderedTrimmedFinalVideo[$keyLyric], 'dummy');
    array_shift($orderedTrimmedFinalVideo[$keyLyric]);
}
echo("<pre>");
echo "   orderedTrimmedFinalVideo\n";
print_r($orderedTrimmedFinalVideo);
echo("</pre>");
//---------------------------------------------------------------------------------------------
//build big word from small words

function build_bigword_from_smallwords($arrayOfConstituents, $originalWord)
{

//firstly remove all the files

    foreach ($arrayOfConstituents as $key => $value) {
        if (!is_array($value)) {
            unset($arrayOfConstituents[$key]);
        }
    }



    echo "original word: $originalWord\n";
    $originalWordArray = str_split($originalWord);
    echo("<pre>");
    echo "  originalWordArray \n";
    print_r($originalWordArray);
    echo("</pre>");
    $fullWordHasBeenFormed = false;
    $iterationOfWhileLoop =0;
    $offset = 0;
    $totalLengthOfSubwordsFoundSofar = 0;
    $reverseMode = false;
    $reverseModeIteration = 0;
    $arrayOfWordsToReturn = array();
    while ($fullWordHasBeenFormed == false) {
        echo "reverse mode: $reverseMode";
        $currentWordTryingToBeFormedArray = array();
        if (!$reverseMode) {
            for ($i=0; $i <= $iterationOfWhileLoop-$offset; $i++) {
                array_push($currentWordTryingToBeFormedArray, $originalWordArray[$i + $offset]);
            }
        } else {
            for ($i=0; $i < $iterationOfWhileLoop-$offset-$reverseModeIteration; $i++) {
                //if ($i + $offset + $reverseModeIteration <= count($originalWordArray) -1) {
                array_push($currentWordTryingToBeFormedArray, $originalWordArray[$i + $offset + $reverseModeIteration]);
                //}
            }
        }

        $currentWordTryingToBeFormed = implode('', $currentWordTryingToBeFormedArray);

        echo("<pre>");
        echo " currentWordTryingToBeFormed:  ";
        print_r($currentWordTryingToBeFormed);
        echo("</pre>");

        foreach ($arrayOfConstituents as $keySubword => $valueSubwordArray) {
            echo "subword: $keySubword\n";

            if ($currentWordTryingToBeFormed === $keySubword) {
                // ie fox = [fox]
                echo "found subword match: $keySubword\n";
                array_push($arrayOfWordsToReturn, $keySubword);
                $offset = strlen($keySubword);//this should be our new offset
                $totalLengthOfSubwordsFoundSofar += strlen($keySubword);
                $reverseMode = false;
                $reverseModeIteration = 0;
            } else {
                echo "no subword match here \n";
                // if (count($originalWordArray) -$offset - $reverseModeIteration == strlen($currentWordTryingToBeFormed)) {
                //     echo "     original word array minus offset equals count of current word    ";
                // }
                // echo "numerical index of this element is ". array_search($keySubword, array_keys($arrayOfConstituents));
                if (array_search($keySubword, array_keys($arrayOfConstituents)) >= count($arrayOfConstituents) -1) {
                    echo "this is the last key";
                }
                //if at end of subword array and none of the full remainder of current words could be formed going up, try going down eg flies lies ies es s
                if (array_search($keySubword, array_keys($arrayOfConstituents)) >= count($arrayOfConstituents) -1 && count($originalWordArray) -$offset - $reverseModeIteration == strlen($currentWordTryingToBeFormed)) {
                    $reverseMode = true;
                }
            }
            //echo "   current offset: $offset\n";
        }


        if ($totalLengthOfSubwordsFoundSofar >= strlen($originalWord)) {
            echo "full word has been formed so no longer searching";
            $fullWordHasBeenFormed = true;
        }

        //this if needs to be able to break the loop if no word was found at all. just put in an empty gap in the folder
        if ($iterationOfWhileLoop >= strlen($originalWord) *2) {
            echo "iteration is greater or equal to original word so no longer searching";
            $fullWordHasBeenFormed = true;
        }

        //ignore the shitty notices fuck them.


        $iterationOfWhileLoop++;
        if ($reverseMode) {
            $reverseModeIteration++;
        }
    }


    return array_unique($arrayOfWordsToReturn);
}
//---------------------------------------------------------------------------------------------
//get the word length in the song, cut out this word being said with slack on either side that is silent or add gaps

function get_length_of_video_file($file)
{
    //echo "\ngetting duraion of $file\n";
    //sometimes the undefined offset errors are caused by corrupt files trying to be scanned for time i think
    $duration = explode(":", explode(",", explode(" ", shell_exec("./ffmpeg -i $file 2>&1 | grep Duration"))[3])[0])[2];
    //echo "\nduration of clip: $duration";
    return $duration;
}

function get_number_of_frames_in_video($file)
{
    return shell_exec("/usr/local/bin/ffprobe -v error -count_frames -select_streams v:0 -show_entries stream=nb_read_frames -of default=nokey=1:noprint_wrappers=1 $file");
}

function get_total_length_of_clips_generated_so_far($numberOfAlternativesToGenerateIndex)
{
    global $channelTitle, $titleOfSong, $amountToAddToEndAndSubtractFromStart, $framerate;

    $path = "./downloadedVideos/$channelTitle/$titleOfSong/botVideoCreation/$numberOfAlternativesToGenerateIndex";
    $clipsGeneratedSoFar = scandir_only_wanted_files($path);
    ksort($clipsGeneratedSoFar, 1);
    $totalLengthSoFar = 0;

    foreach ($clipsGeneratedSoFar as $key => $value) {
        $totalLengthSoFar += get_length_of_video_file("$path/$value");
    }
    return $totalLengthSoFar;
}

function get_total_number_of_frames_so_far($numberOfAlternativesToGenerateIndex)
{
    global $channelTitle, $titleOfSong, $amountToAddToEndAndSubtractFromStart, $framerate;
    $path = "./downloadedVideos/$channelTitle/$titleOfSong/botVideoCreation/$numberOfAlternativesToGenerateIndex";
    $clipsGeneratedSoFar = scandir_only_wanted_files($path);
    ksort($clipsGeneratedSoFar, 1);
    $totalFramesSoFar = 0;

    foreach ($clipsGeneratedSoFar as $key => $value) {
        $totalFramesSoFar += get_number_of_frames_in_video("$path/$value");
    }
    return $totalFramesSoFar;
}

function time_of_first_marker()
{
    global $channelTitle, $titleOfSong, $amountToAddToEndAndSubtractFromStart, $framerate, $markers;

    $startTimeMinutes = explode(":", $markers[0])[1];
    $startTimeSeconds = explode(":", $markers[0])[2];
    $startTimeCentiSeconds = ((explode(" ", explode(":", $markers[0])[3])[0])/$framerate);
    $startTime = $startTimeMinutes*60 + $startTimeSeconds + $startTimeCentiSeconds;
    return $startTime;
}
function cut_lyric_from_clip_and_add_slack($file, $thisMarker, $nextMarker, $keyFromOrderedTrimmedVideoArray, $pathToLongClip, $numberOfAlternativesToGenerateIndex, $nextNextMarker)
{
    global $channelTitle, $titleOfSong, $amountToAddToEndAndSubtractFromStart, $framerate, $shouldKeepAsMuchSlackAsPossible;
    if (is_array($file)) {
        foreach ($file as $key1 => $value1) {
            foreach ($value1 as $key2 => $value2) {
                $file = $value2[0];
            }
        }
    }
    make_if_no_dir("./downloadedVideos/$channelTitle/$titleOfSong/botVideoCreation/$numberOfAlternativesToGenerateIndex");
    $totalLengthOfClipsGeneratedSoFar = get_total_length_of_clips_generated_so_far($numberOfAlternativesToGenerateIndex);
    $totalNumberOfFramesGeneratedSoFar = get_total_number_of_frames_so_far($numberOfAlternativesToGenerateIndex);
    echo "\ntotal length of clips generated so far: $totalLengthOfClipsGeneratedSoFar\n";
    echo "\ntotal number of frames generated so far: $totalNumberOfFramesGeneratedSoFar\n";
    $moreAccurateLengthOfClipsGeneratedSoFar = $totalNumberOfFramesGeneratedSoFar/$framerate;
    //the format is H:m:s:frame
    $startTimeMinutes = explode(":", $thisMarker)[1];
    $startTimeSeconds = explode(":", $thisMarker)[2];
    $startTimeCentiSeconds = ((explode(" ", explode(":", $thisMarker)[3])[0])/$framerate);
    $startTime = $startTimeMinutes*60 + $startTimeSeconds + $startTimeCentiSeconds;

    $endTimeMinutes = explode(":", $nextMarker)[1];
    $endTimeSeconds = explode(":", $nextMarker)[2];
    $endTimeCentiSeconds = ((explode(" ", explode(":", $nextMarker)[3])[0])/$framerate);
    $endTime = $endTimeMinutes*60 + $endTimeSeconds + $endTimeCentiSeconds;
    echo "start time of marker: $startTime\nend time of marker: $endTime\n";
    $lengthWordHasToBe = $endTime - $startTime;

    $startOfWordInLongClip = explode("*", $file)[1];
    $endOfWordInLongClip = explode("*", $file)[2];


    $longClipTheWordIsFrom =  explode("*", $file)[3]."*".explode("*", $file)[4]."*".explode("*", $file)[5];
    $lengthOfActualWord = $endOfWordInLongClip - $startOfWordInLongClip;
    echo "start of word in long clip: $startOfWordInLongClip\nend of word in long clip: $endOfWordInLongClip\nfilename of long clip: $longClipTheWordIsFrom";
    echo "\nkey from ordered trimmed video array: $keyFromOrderedTrimmedVideoArray";
    //judge if we can use the autotuned clip based on slack requirements ie will the autotuned clip allow for enough slack
    //slack should only be applied at the end, not the start of the word
    //$length of word has to be is actuall the length from the markers, its the length the word plus slack has to be
    $slackRequired = $lengthWordHasToBe -$lengthOfActualWord;

    if (explode(" ", $nextMarker)[1] == '-') {
        echo "the next word is actually a gap so we need extra slack";
        $endTimeMinutes = explode(":", $nextNextMarker)[1];
        $endTimeSeconds = explode(":", $nextNextMarker)[2];
        $endTimeCentiSeconds = ((explode(" ", explode(":", $nextNextMarker)[3])[0])/$framerate);
        $endTime = $endTimeMinutes*60 + $endTimeSeconds + $endTimeCentiSeconds;
        echo "\nend time of the gap after this word: $endTime ";
        $lengthWordHasToBe = $endTime - $startTime;
        $slackRequired = $lengthWordHasToBe -$lengthOfActualWord;
    }
    echo "\nwe want length of this word to be $lengthWordHasToBe";
    //startTime is just when this marker should be starting at. id=103842345
    $thisWillActuallyStartAt = time_of_first_marker() + $moreAccurateLengthOfClipsGeneratedSoFar;
    echo "  however when dragged into fcp this clip will really start at $thisWillActuallyStartAt";
    $errorInLength = $thisWillActuallyStartAt - $startTime;
    $lengthWordHasToBe = $lengthWordHasToBe -$errorInLength;
    $slackRequired = $lengthWordHasToBe -$lengthOfActualWord;


    echo "\n\nlength word plus slack has to be: $lengthWordHasToBe\nlength word actually is: $lengthOfActualWord";

    echo "\nslack required: $slackRequired";
    $usingAutotuneFile= false;
    if (file_exists("./downloadedVideos/$channelTitle/$titleOfSong/orderedTrimmedFinalVideo/$keyFromOrderedTrimmedVideoArray/autoTuned/$file")) {
        echo "\nautotune folder exists!";
        $lengthOfAutotunedClip = get_length_of_video_file("./downloadedVideos/$channelTitle/$titleOfSong/orderedTrimmedFinalVideo/$keyFromOrderedTrimmedVideoArray/autoTuned/$file") ;
        if ($lengthOfAutotunedClip -$amountToAddToEndAndSubtractFromStart - $lengthOfActualWord >= $slackRequired) {
            echo " this autotuned clip will provide enough slack ";
            $usingAutotuneFile= true;
        } else {
            echo " this autotuned clip will not provide enough slack";
        }
    } else {
        echo "\nautotune folder doesn't exist";
    }
    //if we aren't using autotune file, then we need to check that the long clip has enough slack, otherwise we need to slow down the slack it provides
    //if we're not using autotune file then we will be using th long clip. therefore it isn't realy necessary to check if there is enough slack...
    if (!$usingAutotuneFile) {
        echo "\nnot using autotune either because there are no autotune files or the clip won't provide enough slack";
        $pathToOriginalLongClip = "$pathToLongClip/$longClipTheWordIsFrom";
        echo "\n\nfull path to file: $pathToOriginalLongClip";

        $lengthOfLongClip = get_length_of_video_file($pathToOriginalLongClip);
        echo "\nlength of long clip: $lengthOfLongClip";
        $slackThisLongClipProvides = $lengthOfLongClip -$startOfWordInLongClip - $lengthOfActualWord;

        if ($slackThisLongClipProvides >= $slackRequired) {
            echo " \nthis long clip will provide enough slack: $slackThisLongClipProvides \n ";
            $willThisLongClipProvideEnoughSlack = true;
        } else {
            echo " \nthis long clip will not provide enough slack: $slackThisLongClipProvides\n";
            $willThisLongClipProvideEnoughSlack = false;
        }



        $timeToEndInLongClip = $endOfWordInLongClip + $slackRequired;
        if ($slackRequired < 0) {
            echo " the word is too long and needs to be shortened \n";
            //$endOfWordInLongClip += 0.05;
            $lengthOfActualWord = $endOfWordInLongClip - $startOfWordInLongClip;
            $videoSpeedAdjust = $lengthWordHasToBe/$lengthOfActualWord;
            $audioSpeedAdjust = $lengthOfActualWord/$lengthWordHasToBe;
            echo "end of word in long clip before changing is $endOfWordInLongClip  ";
            if ($audioSpeedAdjust >2) {
                $audioSpeedAdjust =2;
                $videoSpeedAdjust = 1/$audioSpeedAdjust;
                $endOfWordInLongClip = $lengthWordHasToBe*$audioSpeedAdjust + $startOfWordInLongClip;
                echo "audio speed over 2 so slowing it and changing end of word in long clip to $endOfWordInLongClip  ";
            }

            echo "video speed adjust: $videoSpeedAdjust   audio speed adjust: $audioSpeedAdjust\n";
            echo shell_exec("./ffmpeg  -i $pathToOriginalLongClip -y -ss $startOfWordInLongClip -to $endOfWordInLongClip -r $framerate tempFile-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");
            echo shell_exec("./ffmpeg  -i tempFile-$keyFromOrderedTrimmedVideoArray.mp4 -y -filter_complex '[0:v]setpts=".$videoSpeedAdjust."*PTS[vi];[0:a]atempo=".$audioSpeedAdjust."[a];[vi]scale=1920:1080[v]' -map '[v]' -map '[a]' -r $framerate ./downloadedVideos/$channelTitle/$titleOfSong/botVideoCreation/$numberOfAlternativesToGenerateIndex/$keyFromOrderedTrimmedVideoArray=$file 2>&1");
            unlink("tempFile-$keyFromOrderedTrimmedVideoArray.mp4");
        } else {
            $timeToEndInLongClip = $endOfWordInLongClip + max($slackRequired, 0.1);

            //$endOfWordInLongClip += 0.05; //its too risky to do this so nahhh
            if ($willThisLongClipProvideEnoughSlack) {
                if (!$shouldKeepAsMuchSlackAsPossible) {
                    echo shell_exec("./ffmpeg  -i $pathToOriginalLongClip -y -ss $startOfWordInLongClip -to $endOfWordInLongClip -r $framerate -vf scale=1920:1080 tempFile1-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");
                    echo shell_exec("./ffmpeg  -i $pathToOriginalLongClip -y -ss $endOfWordInLongClip -to $timeToEndInLongClip -an -r $framerate -vf scale=1920:1080 tempFile2-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");
                    echo shell_exec("./ffmpeg  -f lavfi -i anullsrc=channel_layout=stereo:sample_rate=44100 -i tempFile2-$keyFromOrderedTrimmedVideoArray.mp4 -y -shortest -c:v copy -c:a aac -r $framerate tempFile3-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");

                    echo shell_exec("./ffmpeg  -i tempFile1-$keyFromOrderedTrimmedVideoArray.mp4 -i tempFile3-$keyFromOrderedTrimmedVideoArray.mp4 -y -filter_complex '[0:0][0:1][1:0][1:1]concat=n=2:v=1:a=1 [v] [a]' -map '[v]' -map '[a]' -r $framerate ./downloadedVideos/$channelTitle/$titleOfSong/botVideoCreation/$numberOfAlternativesToGenerateIndex/$keyFromOrderedTrimmedVideoArray=$file 2>&1");


                    unlink("tempFile1-$keyFromOrderedTrimmedVideoArray.mp4");
                    unlink("tempFile2-$keyFromOrderedTrimmedVideoArray.mp4");
                    unlink("tempFile3-$keyFromOrderedTrimmedVideoArray.mp4");
                } else {
                    $shiftAmount = max(0, $startOfWordInLongClip - .1);
                    $startOfWordInLongClip -= $shiftAmount;
                    $timeToEndInLongClip -= $shiftAmount;
                    echo shell_exec("./ffmpeg  -i $pathToOriginalLongClip -y -ss $startOfWordInLongClip -to $timeToEndInLongClip -r $framerate -vf scale=1920:1080 ./downloadedVideos/$channelTitle/$titleOfSong/botVideoCreation/$numberOfAlternativesToGenerateIndex/$keyFromOrderedTrimmedVideoArray=$file 2>&1");
                }
            } else {
                if (!$shouldKeepAsMuchSlackAsPossible) {
                    echo "not doing the normal ffmpeg routine coz not enough slack. gonna have to stretch it out.";
                    //get the bit of the clip after the actual word has finished, cut that out and stretch it so there is enough slack.
                    echo shell_exec("./ffmpeg  -i $pathToOriginalLongClip -y -ss $startOfWordInLongClip -to $endOfWordInLongClip -r $framerate -vf scale=1920:1080  tempFile1-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");

                    echo "\n\ncutting available slack from: $endOfWordInLongClip  to: $lengthOfLongClip\n\n";
                    echo shell_exec("./ffmpeg  -i $pathToOriginalLongClip -y -ss $endOfWordInLongClip -t $lengthOfLongClip -an -r $framerate tempFile2-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");
                    $lengthOfAvailableSlack = get_length_of_video_file("tempFile2-$keyFromOrderedTrimmedVideoArray.mp4");
                    echo "\nlength of available slack clip: $lengthOfAvailableSlack\n";
                    //the long clip needs to be stretched from current duration to current duration + (slack required - slack provided)
                    //wait no that's wrong. only the current provided slack should be stretched, not the whole clip.
                    //therefore the current slack should be stretched to the required slack. then this slack is concatenated.
                    $videoSpeedAdjust = $slackRequired/$lengthOfAvailableSlack;
                    echo shell_exec("./ffmpeg  -i tempFile2-$keyFromOrderedTrimmedVideoArray.mp4 -y -filter_complex '[0:v]setpts=".$videoSpeedAdjust."*PTS[vi];[vi]scale=1920:1080[v]' -map '[v]' -r $framerate  tempFile3-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");
                    echo shell_exec("./ffmpeg  -f lavfi -i anullsrc=channel_layout=stereo:sample_rate=44100 -i tempFile3-$keyFromOrderedTrimmedVideoArray.mp4 -y -shortest -c:v copy -c:a aac -r $framerate  tempFile4-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");
                    echo shell_exec("./ffmpeg  -i tempFile1-$keyFromOrderedTrimmedVideoArray.mp4 -i tempFile4-$keyFromOrderedTrimmedVideoArray.mp4 -y -filter_complex '[0:0][0:1][1:0][1:1]concat=n=2:v=1:a=1 [v] [a]' -map '[v]' -map '[a]' -r $framerate ./downloadedVideos/$channelTitle/$titleOfSong/botVideoCreation/$numberOfAlternativesToGenerateIndex/$keyFromOrderedTrimmedVideoArray=$file 2>&1");
                    unlink("tempFile1-$keyFromOrderedTrimmedVideoArray.mp4");
                    unlink("tempFile2-$keyFromOrderedTrimmedVideoArray.mp4");
                    unlink("tempFile3-$keyFromOrderedTrimmedVideoArray.mp4");
                    unlink("tempFile4-$keyFromOrderedTrimmedVideoArray.mp4");
                } else {
                    $shiftAmount = max(0, $startOfWordInLongClip - .1);
                    $startOfWordInLongClip -= $shiftAmount;
                    $timeToEndInLongClip -= $shiftAmount;
                    echo shell_exec("./ffmpeg  -i $pathToOriginalLongClip -y -ss $startOfWordInLongClip -to $timeToEndInLongClip -r $framerate -vf scale=1920:1080 ./downloadedVideos/$channelTitle/$titleOfSong/botVideoCreation/$numberOfAlternativesToGenerateIndex/$keyFromOrderedTrimmedVideoArray=$file 2>&1");
                }
            }
        }
    } else {
        //this is for when we should use the autotune file
        echo "\nusing autotune file";
        $pathToOriginalLongClip = "./downloadedVideos/$channelTitle/$titleOfSong/orderedTrimmedFinalVideo/$keyFromOrderedTrimmedVideoArray/autoTuned/$file";
        echo "\n\nfull path to file: $pathToOriginalLongClip";

        //long clip here isn't actually long clip just cba to change var names.
        $duration = $endOfWordInLongClip - $startOfWordInLongClip;
        $startOfWordInLongClip = $amountToAddToEndAndSubtractFromStart;
        $endOfWordInLongClip = $duration + $startOfWordInLongClip;

        $timeToEndInLongClip = $endOfWordInLongClip + $slackRequired;

        if ($slackRequired < 0) {
            echo " the word is too long and needs to be shortened \n";
            //$endOfWordInLongClip += 0.05;
            $lengthOfActualWord = $endOfWordInLongClip - $startOfWordInLongClip;
            $videoSpeedAdjust = $lengthWordHasToBe/$lengthOfActualWord;
            $audioSpeedAdjust = $lengthOfActualWord/$lengthWordHasToBe;
            echo "end of word in long clip before changing is $endOfWordInLongClip  ";
            if ($audioSpeedAdjust >2) {
                $audioSpeedAdjust =2;
                $videoSpeedAdjust = 1/$audioSpeedAdjust;
                $endOfWordInLongClip = $lengthWordHasToBe*$audioSpeedAdjust + $startOfWordInLongClip;
                echo "audio speed over 2 so slowing it and changing end of word in long clip to $endOfWordInLongClip  ";
            }
            //
            echo "video speed adjust: $videoSpeedAdjust   audio speed adjust: $audioSpeedAdjust\n";
            echo shell_exec("./ffmpeg  -i $pathToOriginalLongClip -y -ss $startOfWordInLongClip -to $endOfWordInLongClip -r $framerate tempFile-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");
            echo shell_exec("./ffmpeg  -i tempFile-$keyFromOrderedTrimmedVideoArray.mp4 -y -filter_complex '[0:v]setpts=".$videoSpeedAdjust."*PTS[vi];[0:a]atempo=".$audioSpeedAdjust."[a];[vi]scale=1920:1080[v]' -map '[v]' -map '[a]' -r $framerate ./downloadedVideos/$channelTitle/$titleOfSong/botVideoCreation/$numberOfAlternativesToGenerateIndex/$keyFromOrderedTrimmedVideoArray=$file 2>&1");
            unlink("tempFile-$keyFromOrderedTrimmedVideoArray.mp4");
        } else {
            if (!$shouldKeepAsMuchSlackAsPossible) {

            //$endOfWordInLongClip += 0.05; //its too risky to do this so nahhh
                echo shell_exec("./ffmpeg  -i $pathToOriginalLongClip -y -ss $startOfWordInLongClip -to $endOfWordInLongClip -r $framerate -vf scale=1920:1080 tempFile1-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");
                echo shell_exec("./ffmpeg  -i $pathToOriginalLongClip -y -ss $endOfWordInLongClip -to $timeToEndInLongClip -an -r $framerate -vf scale=1920:1080 tempFile2-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");
                echo shell_exec("./ffmpeg  -f lavfi -i anullsrc=channel_layout=stereo:sample_rate=44100 -i tempFile2-$keyFromOrderedTrimmedVideoArray.mp4 -y -shortest -c:v copy -c:a aac -r $framerate tempFile3-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");

                echo shell_exec("./ffmpeg  -i tempFile1-$keyFromOrderedTrimmedVideoArray.mp4 -i tempFile3-$keyFromOrderedTrimmedVideoArray.mp4 -y -filter_complex '[0:0][0:1][1:0][1:1]concat=n=2:v=1:a=1 [v] [a]' -map '[v]' -map '[a]' -r $framerate  ./downloadedVideos/$channelTitle/$titleOfSong/botVideoCreation/$numberOfAlternativesToGenerateIndex/$keyFromOrderedTrimmedVideoArray=$file 2>&1");

                unlink("tempFile1-$keyFromOrderedTrimmedVideoArray.mp4");
                unlink("tempFile2-$keyFromOrderedTrimmedVideoArray.mp4");
                unlink("tempFile3-$keyFromOrderedTrimmedVideoArray.mp4");
            } else {
                $shiftAmount = max(0, $startOfWordInLongClip - .1);
                $startOfWordInLongClip -= $shiftAmount;
                $timeToEndInLongClip -= $shiftAmount;
                echo shell_exec("./ffmpeg  -i $pathToOriginalLongClip -y -ss $startOfWordInLongClip -to $timeToEndInLongClip -r $framerate -vf scale=1920:1080 ./downloadedVideos/$channelTitle/$titleOfSong/botVideoCreation/$numberOfAlternativesToGenerateIndex/$keyFromOrderedTrimmedVideoArray=$file 2>&1");
            }
        }
    }
}
//---------------------------------------------------------------------------------------------
//now the same thing but for subwords
function cut_lyric_from_clip_and_add_slack_subwords($files, $thisMarker, $nextMarker, $keyFromOrderedTrimmedVideoArray, $pathsToLongClips, $numberOfAlternativesToGenerateIndex, $nextNextMarker)
{
    global $channelTitle, $titleOfSong, $amountToAddToEndAndSubtractFromStart,$framerate;
    make_if_no_dir("./downloadedVideos/$channelTitle/$titleOfSong/botVideoCreation/$numberOfAlternativesToGenerateIndex");
    $totalLengthOfClipsGeneratedSoFar = get_total_length_of_clips_generated_so_far($numberOfAlternativesToGenerateIndex);
    $totalNumberOfFramesGeneratedSoFar = get_total_number_of_frames_so_far($numberOfAlternativesToGenerateIndex);
    echo "\ntotal length of clips generated so far: $totalLengthOfClipsGeneratedSoFar\n";
    echo "\ntotal number of frames generated so far: $totalNumberOfFramesGeneratedSoFar\n";
    $moreAccurateLengthOfClipsGeneratedSoFar = $totalNumberOfFramesGeneratedSoFar/$framerate;
    echo "\ntotal length of clips generated so far: $totalLengthOfClipsGeneratedSoFar\n";
    echo("<pre>");
    echo "array of files that make up this word: ";
    print_r($files);
    echo("</pre>");

    echo("<pre>");
    echo "   array of paths that make up this word\n";
    print_r($pathsToLongClips);
    echo("</pre>");


    //the format is H:m:s:frame
    $startTimeMinutes = explode(":", $thisMarker)[1];
    $startTimeSeconds = explode(":", $thisMarker)[2];
    $startTimeCentiSeconds = ((explode(" ", explode(":", $thisMarker)[3])[0])/$framerate);
    $startTime = $startTimeMinutes*60 + $startTimeSeconds + $startTimeCentiSeconds;

    $endTimeMinutes = explode(":", $nextMarker)[1];
    $endTimeSeconds = explode(":", $nextMarker)[2];
    $endTimeCentiSeconds = ((explode(" ", explode(":", $nextMarker)[3])[0])/$framerate);
    $endTime = $endTimeMinutes*60 + $endTimeSeconds + $endTimeCentiSeconds;
    echo "start time of marker: $startTime\nend time of marker: $endTime\n";
    $lengthWordHasToBe = $endTime - $startTime; //this is the length of the whole word, not the individual subwords.

    $arrayWithInfoOnTheSubWord = array();
    $totalLengthOfActualSubwords = 0;
    $totalCharLengthOfSubwords = 0;
    foreach ($files as $keyFile => $valueFileNameLongClip) {
        $startOfWordInLongClip = explode("*", $valueFileNameLongClip)[1];
        $endOfWordInLongClip = explode("*", $valueFileNameLongClip)[2];
        $longClipTheWordIsFrom =  explode("*", $valueFileNameLongClip)[3]."*".explode("*", $valueFileNameLongClip)[4]."*".explode("*", $valueFileNameLongClip)[5];
        $lengthOfActualSubWord = $endOfWordInLongClip - $startOfWordInLongClip;
        $totalLengthOfActualSubwords += $lengthOfActualSubWord;
        $totalCharLengthOfSubwords += strlen(explode('-', $valueFileNameLongClip)[0]);
        array_push($arrayWithInfoOnTheSubWord, array('startOfWordInLongClip'=> $startOfWordInLongClip, 'endOfWordInLongClip' =>$endOfWordInLongClip,  'lengthOfActualSubWord' => $lengthOfActualSubWord, 'thisSubwordLength' => strlen(explode('-', $valueFileNameLongClip)[0])));
    }


    $slackRequired = $lengthWordHasToBe -$totalLengthOfActualSubwords;

    echo("<pre>");
    echo " arrayWithInfoOnTheSubWord  \n";
    print_r($arrayWithInfoOnTheSubWord);
    echo("</pre>");
    //
    if (explode(" ", $nextMarker)[1] == '-') {
        echo "the next word is actually a gap so we need extra slack";
        $endTimeMinutes = explode(":", $nextNextMarker)[1];
        $endTimeSeconds = explode(":", $nextNextMarker)[2];
        $endTimeCentiSeconds = ((explode(" ", explode(":", $nextNextMarker)[3])[0])/$framerate);
        $endTime = $endTimeMinutes*60 + $endTimeSeconds + $endTimeCentiSeconds;
        echo "\nend time of the gap after this word: $endTime ";
        $lengthWordHasToBe = $endTime - $startTime;
        $slackRequired = $lengthWordHasToBe -$totalLengthOfActualSubwords;
    }
    echo "\nwe want length of this word to be $lengthWordHasToBe";
    //startTime is just when this marker should be starting at. id=103842345
    $thisWillActuallyStartAt = time_of_first_marker() + $moreAccurateLengthOfClipsGeneratedSoFar;
    echo "  however when dragged into fcp this clip will really start at $thisWillActuallyStartAt";
    $errorInLength = $thisWillActuallyStartAt - $startTime;
    $lengthWordHasToBe = $lengthWordHasToBe -$errorInLength;
    $slackRequired = $lengthWordHasToBe -$totalLengthOfActualSubwords;

    echo "\n\nlength of word plus slack has to be: $lengthWordHasToBe\ntotal length of subwords actually is: $totalLengthOfActualSubwords\ntotal number of characters of all subwords: $totalCharLengthOfSubwords";

    //it should only add slack to the last subword. The subwords before that should be exactly adjacent.
    //find the fractions of the 'word time' of the whole length of word with slack, then divide the subwords into the correct times
    //we don't have a var for word time , so we just need to append the subwords, or if they append to be too long then speed them up with the appropriate fractions of time.
    //fuck it the fraction of time of each subword should just be based on its current length dont wanna fuck shit up ygm
    echo "\nslack required: $slackRequired";

    if ($slackRequired < 0) {
        echo " the subwords are too long and needs to be shortened \n";
        $inputStringForConcat = "";
        $concatString = "";
        $concateNvalue = 0;
        //ok, so for these subwords, it sounds weird if we squash them just enough to fit the length it needs to be plus slack when there is a gap next. the slack should be there as quite slack.
        //i just realised this means for non subwords we may have the same problem. basically detect if the next word is a gap and if it is and we need to squash then use the end of this actual word instead of the end of the following gap.
        //you know what, seriously fuck this. i mean, how often are we gonna have to speed up a video AND have a gap as the next word? It's pretty fucking rare tbh, it can be resolved when editing the words in fcp if it does ever pop up.
        foreach ($arrayWithInfoOnTheSubWord as $keySubwordInfo => $valueSubwordInfoArray) {
            $nameOfSubword = explode('-', $files[$keySubwordInfo])[0];

            $lengthOfThisSubwordShouldBeCompressedTo = ($valueSubwordInfoArray['lengthOfActualSubWord']/$totalLengthOfActualSubwords)*$lengthWordHasToBe;
            echo "\n\nlength this subword should be compressed to: $lengthOfThisSubwordShouldBeCompressedTo";
            $pathToOriginalLongClip = "$pathsToLongClips[$keySubwordInfo]/".   explode("*", $files[$keySubwordInfo])[3]."*".explode("*", $files[$keySubwordInfo])[4]."*".explode("*", $files[$keySubwordInfo])[5];

            echo "\nfull path to original long clip file: $pathToOriginalLongClip";

            $lengthOfLongClip = get_length_of_video_file($pathToOriginalLongClip);
            echo "\nlength of long clip: $lengthOfLongClip";

            $endOfWordInLongClip = $valueSubwordInfoArray['endOfWordInLongClip'];
            $startOfWordInLongClip = $valueSubwordInfoArray['startOfWordInLongClip'];
            $lengthOfActualWord = $endOfWordInLongClip - $startOfWordInLongClip;

            echo shell_exec("./ffmpeg  -i $pathToOriginalLongClip -y -ss $startOfWordInLongClip -to $endOfWordInLongClip -r $framerate tempFile1-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");
            $lengthOfCutClip = get_length_of_video_file("./tempFile1-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4");
            echo "\nthe length of the cut clip turned out to be $lengthOfCutClip despite being calulated as $lengthOfActualWord\n";

            $lengthOfActualWord = $lengthOfCutClip;
            $videoSpeedAdjust = $lengthOfThisSubwordShouldBeCompressedTo/$lengthOfActualWord;
            $audioSpeedAdjust = $lengthOfActualWord/$lengthOfThisSubwordShouldBeCompressedTo;
            if ($audioSpeedAdjust >1.95) {
                echo "\nend of word in long clip before changing is $endOfWordInLongClip  ";

                $audioSpeedAdjust =1.95;
                $videoSpeedAdjust = 1/$audioSpeedAdjust;
                $endOfWordInLongClip = $lengthOfThisSubwordShouldBeCompressedTo*$audioSpeedAdjust + $startOfWordInLongClip;
                echo "audio speed over 2 so slowing it and changing end of word in long clip to $endOfWordInLongClip  ";
            }
            echo "\nvideo speed adjust: $videoSpeedAdjust   audio speed adjust: $audioSpeedAdjust\n";
            echo shell_exec("./ffmpeg  -i tempFile1-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 -y -filter_complex '[0:v]setpts=".$videoSpeedAdjust."*PTS[vi];[0:a]atempo=".$audioSpeedAdjust."[a];[vi]scale=1920:1080[v]' -map '[v]' -map '[a]' -r $framerate tempFile2-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");
            unlink("tempFile1-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4");
            $inputStringForConcat .= " -i "."tempFile2-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4";
            $concatString .= "[$keySubwordInfo:0][$keySubwordInfo:1]";
            $concateNvalue++;
        }
        $concatString .= " concat=n=$concateNvalue:v=1:a=1[v][a]";
        echo shell_exec("./ffmpeg $inputStringForConcat -filter_complex '$concatString' -map '[v]' -map '[a]' -r $framerate  tempFile3-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");

        $videoSpeedAdjustFinalWholeWord = $lengthWordHasToBe/get_length_of_video_file("tempFile3-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4");
        $audioSpeedAdjustFinalWholeWord = 1/$videoSpeedAdjustFinalWholeWord;
        echo shell_exec("./ffmpeg  -i tempFile3-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 -y -filter_complex '[0:v]setpts=".$videoSpeedAdjustFinalWholeWord."*PTS[v];[0:a]atempo=".$audioSpeedAdjustFinalWholeWord."[a]' -map '[v]' -map '[a]' -r $framerate ./downloadedVideos/$channelTitle/$titleOfSong/botVideoCreation/$numberOfAlternativesToGenerateIndex/$keyFromOrderedTrimmedVideoArray-madeFromArrayOfSubwords.mp4 2>&1");
        unlink("tempFile3-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4");

        foreach ($arrayWithInfoOnTheSubWord as $keySubwordInfo => $value) {
            $nameOfSubword = explode('-', $files[$keySubwordInfo])[0];

            unlink("tempFile2-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4");
        }
    } else {
        //here we should just add the word part of all the subwords and only add slack to the last one
        echo "\ntotal subword length is less than required length with slack so we're gonna add slack";
        $lengthOfTotalSubwordsSoFar = 0;
        $inputStringForConcat = "";
        $concatString = "";
        $concateNvalue = 0;
        foreach ($arrayWithInfoOnTheSubWord as $keySubwordInfo => $valueSubwordInfoArray) {
            $pathToOriginalLongClip = "$pathsToLongClips[$keySubwordInfo]/".   explode("*", $files[$keySubwordInfo])[3]."*".explode("*", $files[$keySubwordInfo])[4]."*".explode("*", $files[$keySubwordInfo])[5];
            $nameOfSubword = explode('-', $files[$keySubwordInfo])[0];

            echo "\n\nfull path to original long clip file: $pathToOriginalLongClip";

            $lengthOfLongClip = get_length_of_video_file($pathToOriginalLongClip);
            echo "\nlength of long clip: $lengthOfLongClip";

            $endOfWordInLongClip = $valueSubwordInfoArray['endOfWordInLongClip'];
            $startOfWordInLongClip = $valueSubwordInfoArray['startOfWordInLongClip'];
            $lengthOfActualWord = $endOfWordInLongClip - $startOfWordInLongClip;
            //$timeToEndInLongClip = $endOfWordInLongClip + $slackRequired; // don't use this since ffmpeg won't cut the subwords accurately so we need to get the time each subword was cut to to determine a new slack requirement and new time to cut the last long subword clip to.

            $slackThisLongClipProvides = $lengthOfLongClip -$valueSubwordInfoArray['startOfWordInLongClip'] - $valueSubwordInfoArray['lengthOfActualSubWord'];

            if ($valueSubwordInfoArray == end($arrayWithInfoOnTheSubWord)) {
                echo "\nthis is the end of the subword array and the long clip provides this much slack: $slackThisLongClipProvides \n";
                //since it's the end, we should do the slack adding here

                if ($slackThisLongClipProvides >= $slackRequired) {
                    echo " \nthis long clip (final subword) will provide enough slack: $slackThisLongClipProvides \n ";
                    $willThisLongClipProvideEnoughSlack = true;
                } else {
                    echo " \nthis long clip (final subword) will not provide enough slack: $slackThisLongClipProvides\n";
                    $willThisLongClipProvideEnoughSlack = false;
                }
                if ($willThisLongClipProvideEnoughSlack) {
                    echo shell_exec("./ffmpeg  -i $pathToOriginalLongClip -y -ss $startOfWordInLongClip -to $endOfWordInLongClip -r $framerate -vf scale=1920:1080 tempFile1-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");
                    $lengthOfThisSubwordCut = get_length_of_video_file("tempFile1-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4");
                    $lengthOfTotalSubwordsSoFar += $lengthOfThisSubwordCut;

                    $newSlackRequirementAfterTakingIntoAccountActualSubwordLengths = $lengthWordHasToBe - $lengthOfTotalSubwordsSoFar;
                    $timeToEndInLongClip = max($newSlackRequirementAfterTakingIntoAccountActualSubwordLengths, 0.1) +$endOfWordInLongClip;
                    echo "\nlnew slack requirement thanks to ffmpeg's shitty cutting accuracy: $newSlackRequirementAfterTakingIntoAccountActualSubwordLengths\n";
                    //this next shell exec is for cutting out the slack
                    echo shell_exec("./ffmpeg  -i $pathToOriginalLongClip -y -ss $endOfWordInLongClip -to $timeToEndInLongClip -an -r $framerate -vf scale=1920:1080 tempFile2-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");
                    echo shell_exec("./ffmpeg  -f lavfi -i anullsrc=channel_layout=stereo:sample_rate=44100 -i  tempFile2-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 -y -shortest -c:v copy -c:a aac -r $framerate tempFile3-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");

                    echo shell_exec("./ffmpeg  -i tempFile1-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 -i tempFile3-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 -y -filter_complex '[0:0][0:1][1:0][1:1]concat=n=2:v=1:a=1 [v] [a]' -map '[v]' -map '[a]' -r $framerate tempFile4-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");
                    unlink("tempFile2-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4");
                    unlink("tempFile3-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4");
                    $stringToUnlinkWhenDone = "tempFile4-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4";

                    //this is to concat the word with slack (since they were just concatenated above)
                    $inputStringForConcat .= " -i "."tempFile4-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4";
                    $concatString .= "[$keySubwordInfo:0][$keySubwordInfo:1]";
                    $concateNvalue++;
                } else {
                    //final clip will not provide enough slack so we need to stttreeeeetcchhh
                    echo "not doing the normal ffmpeg routine coz not enough slack. gonna have to stretch it out.";
                    //get the bit of the clip after the actual word has finished, cut that out and stretch it so there is enough slack.
                    echo shell_exec("./ffmpeg  -i $pathToOriginalLongClip -y -ss $startOfWordInLongClip -to $endOfWordInLongClip -r $framerate -vf scale=1920:1080 tempFile1-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");
                    $lengthOfThisSubwordCut = get_length_of_video_file("tempFile1-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4");
                    $lengthOfTotalSubwordsSoFar += $lengthOfThisSubwordCut;

                    $newSlackRequirementAfterTakingIntoAccountActualSubwordLengths = $lengthWordHasToBe - $lengthOfTotalSubwordsSoFar;
                    $timeToEndInLongClip = $newSlackRequirementAfterTakingIntoAccountActualSubwordLengths +$endOfWordInLongClip;

                    echo shell_exec("./ffmpeg  -i $pathToOriginalLongClip -y -ss $endOfWordInLongClip -t $lengthOfLongClip -an -r $framerate tempFile2-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");
                    $lengthOfAvailableSlack = get_length_of_video_file("tempFile2-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4");
                    echo "\nlength of available slack clip: $lengthOfAvailableSlack\n";
                    //the long clip needs to be stretched from current duration to current duration + (slack required - slack provided)
                    //wait no that's wrong. only the current provided slack should be stretched, not the whole clip.
                    //therefore the current slack should be stretched to the required slack. then this slack is concatenated.
                    $videoSpeedAdjust = $newSlackRequirementAfterTakingIntoAccountActualSubwordLengths/$lengthOfAvailableSlack;
                    //since its only video we are stretchig, there is no limit to how slow it can be unlike atempo so no need to do fancy loops to accomodate the slowness
                    echo shell_exec("./ffmpeg  -i tempFile2-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 -y -filter_complex '[0:v]setpts=".$videoSpeedAdjust."*PTS[vi];[vi]scale=1920:1080[v]' -map '[v]'  -r $framerate tempFile3-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");
                    echo shell_exec("./ffmpeg  -f lavfi -i anullsrc=channel_layout=stereo:sample_rate=44100 -i tempFile3-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 -y -shortest -c:v copy -c:a aac -r $framerate tempFile4-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");
                    echo shell_exec("./ffmpeg  -i tempFile1-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 -i tempFile4-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 -y -filter_complex '[0:0][0:1][1:0][1:1]concat=n=2:v=1:a=1 [v] [a]' -map '[v]' -map '[a]' -r $framerate tempFile5-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");


                    unlink("tempFile2-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4");
                    unlink("tempFile3-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4");
                    unlink("tempFile4-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4");

                    $stringToUnlinkWhenDone = "tempFile5-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4";

                    $inputStringForConcat .= " -i "."tempFile5-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4";
                    $concatString .= "[$keySubwordInfo:0][$keySubwordInfo:1]";
                    $concateNvalue++;
                }
            } else {
                //if this isn't the last subword, just cut out the actual word. tempfile1 are the cut subwords that arent the last one
                echo shell_exec("./ffmpeg  -i $pathToOriginalLongClip -y -ss $startOfWordInLongClip -to $endOfWordInLongClip -r $framerate -vf scale=1920:1080 tempFile1-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");
                $lengthOfThisSubwordCut = get_length_of_video_file("tempFile1-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4");
                $lengthOfTotalSubwordsSoFar += $lengthOfThisSubwordCut;

                $inputStringForConcat .= " -i "."tempFile1-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4";
                $concatString .= "[$keySubwordInfo:0][$keySubwordInfo:1]";
                $concateNvalue++;
            }
        }
        $concatString .= " concat=n=$concateNvalue:v=1:a=1[v][a]";
        echo shell_exec("./ffmpeg $inputStringForConcat -filter_complex '$concatString' -map '[v]' -map '[a]'  -r $framerate tempFile5-concatenated-$keyFromOrderedTrimmedVideoArray.mp4 2>&1");

        $videoSpeedAdjustFinalWholeWord = $lengthWordHasToBe/get_length_of_video_file("tempFile5-concatenated-$keyFromOrderedTrimmedVideoArray.mp4");
        $audioSpeedAdjustFinalWholeWord = 1/$videoSpeedAdjustFinalWholeWord;
        echo shell_exec("./ffmpeg  -i tempFile5-concatenated-$keyFromOrderedTrimmedVideoArray.mp4 -y -filter_complex '[0:v]setpts=".$videoSpeedAdjustFinalWholeWord."*PTS[v];[0:a]atempo=".$audioSpeedAdjustFinalWholeWord."[a]' -map '[v]' -map '[a]' -r $framerate ./downloadedVideos/$channelTitle/$titleOfSong/botVideoCreation/$numberOfAlternativesToGenerateIndex/$keyFromOrderedTrimmedVideoArray-madeFromArrayOfSubwords.mp4 2>&1");
        unlink("tempFile5-concatenated-$keyFromOrderedTrimmedVideoArray.mp4");

        foreach ($arrayWithInfoOnTheSubWord as $keySubwordInfo => $value) {
            $nameOfSubword = explode('-', $files[$keySubwordInfo])[0];

            unlink("tempFile1-$keySubwordInfo-$nameOfSubword-$keyFromOrderedTrimmedVideoArray.mp4");
        }
        unlink($stringToUnlinkWhenDone);
    }
}

function empty_space_generator($thisMarker, $nextMarker, $keyFromOrderedTrimmedVideoArray, $numberOfAlternativesToGenerateIndex, $nextNextMarker)
{
    echo "\nempty space generator\n";
    global $channelTitle, $titleOfSong, $amountToAddToEndAndSubtractFromStart,$framerate;
    make_if_no_dir("./downloadedVideos/$channelTitle/$titleOfSong/botVideoCreation/$numberOfAlternativesToGenerateIndex");
    $totalLengthOfClipsGeneratedSoFar = get_total_length_of_clips_generated_so_far($numberOfAlternativesToGenerateIndex);
    $totalNumberOfFramesGeneratedSoFar = get_total_number_of_frames_so_far($numberOfAlternativesToGenerateIndex);
    echo "\ntotal length of clips generated so far: $totalLengthOfClipsGeneratedSoFar\n";
    echo "\ntotal number of frames generated so far: $totalNumberOfFramesGeneratedSoFar\n";
    $moreAccurateLengthOfClipsGeneratedSoFar = $totalNumberOfFramesGeneratedSoFar/$framerate;
    echo "\ntotal length of clips generated so far: $totalLengthOfClipsGeneratedSoFar\n";
    //the format is H:m:s:frame
    $startTimeMinutes = explode(":", $thisMarker)[1];
    $startTimeSeconds = explode(":", $thisMarker)[2];
    $startTimeCentiSeconds = ((explode(" ", explode(":", $thisMarker)[3])[0])/$framerate);
    $startTime = $startTimeMinutes*60 + $startTimeSeconds + $startTimeCentiSeconds;

    $endTimeMinutes = explode(":", $nextMarker)[1];
    $endTimeSeconds = explode(":", $nextMarker)[2];
    $endTimeCentiSeconds = ((explode(" ", explode(":", $nextMarker)[3])[0])/$framerate);
    $endTime = $endTimeMinutes*60 + $endTimeSeconds + $endTimeCentiSeconds;
    echo "start time of marker: $startTime\nend time of marker: $endTime\n";
    $lengthWordHasToBe = $endTime - $startTime; //this is the length of the whole word, not the individual subwords.



    if (explode(" ", $nextMarker)[1] == '-') {
        echo "the next word is actually a gap so we need extra slack";
        $endTimeMinutes = explode(":", $nextNextMarker)[1];
        $endTimeSeconds = explode(":", $nextNextMarker)[2];
        $endTimeCentiSeconds = ((explode(" ", explode(":", $nextNextMarker)[3])[0])/$framerate);
        $endTime = $endTimeMinutes*60 + $endTimeSeconds + $endTimeCentiSeconds;
        echo "\nend time of the gap after this word: $endTime ";
        $lengthWordHasToBe = $endTime - $startTime;
    }
    echo "\nwe want length of this word to be $lengthWordHasToBe";
    //startTime is just when this marker should be starting at. id=103842345
    $thisWillActuallyStartAt = time_of_first_marker() + $moreAccurateLengthOfClipsGeneratedSoFar;
    echo "  however when dragged into fcp this clip will really start at $thisWillActuallyStartAt";
    $errorInLength = $thisWillActuallyStartAt - $startTime;
    $lengthWordHasToBe = $lengthWordHasToBe -$errorInLength;

    echo "\n\nlength of word plus slack has to be: $lengthWordHasToBe";
    echo shell_exec("./ffmpeg -f lavfi -i color=c=red:s=1920x1080:d=$lengthWordHasToBe -r $framerate ./downloadedVideos/$channelTitle/$titleOfSong/botVideoCreation/$numberOfAlternativesToGenerateIndex/$keyFromOrderedTrimmedVideoArray-emptySpaceGenerator.mp4 2>&1");
}

//---------------------------------------------------------------------------------------------
//go through the markers and call functions
for ($indexOfWordInFolder=0; $indexOfWordInFolder < $numberOfAlternativesToGenerate; $indexOfWordInFolder++) {
    $numberOfGapsSoFar = 0;

    foreach ($markers as $keyMarkers => $valueMarkers) {
        echo "\n//---------------------------------------------------------------------------------------------\n";

        $keyIgnoringGaps = $keyMarkers-$numberOfGapsSoFar;
        echo "we are getting this index of the word in the folder: $indexOfWordInFolder";
        echo "\n". $valueMarkers;
        if (explode(" ", $valueMarkers)[1] == '-') {
            echo "this is a gap \n";
            $numberOfGapsSoFar++;
            echo "\n//---------------------------------------------------------------------------------------------\n";
            continue;
        }

        $lyricArrayShell1= $orderedTrimmedFinalVideo[array_keys($orderedTrimmedFinalVideo)[$keyIgnoringGaps]];
        echo("<pre>");
        echo " for key $keyIgnoringGaps \n";
        print_r($lyricArrayShell1);
        echo("</pre>");
        //needs to loop and look for actual files because the first element may not actually be a file but we dont wanna use whats in that folder we want to try and get a file first.
        //actually, the array key will have a number if the value is a file, and a word if its not a file, so we don't need to care about this.
        //we don't even need to detect the 'mp4' in the name because we know that if the key is a number then the value HAS to be a file! brilliant!
        //therefore we just need to check if the array element is actually set, because if it isnt a number then there will be no array elements found :)
        if (isset($orderedTrimmedFinalVideo[array_keys($orderedTrimmedFinalVideo)[$keyIgnoringGaps]][$indexOfWordInFolder])) {
            //file would look like lyric-0*start*end*from  the start and end times are the exact times the word starts and ends in the longer clip, the 'from' bit at the end
            echo "this is a file \n";
            cut_lyric_from_clip_and_add_slack($lyricArrayShell1[$indexOfWordInFolder], $valueMarkers, $markers[$keyMarkers+1], array_keys($orderedTrimmedFinalVideo)[$keyIgnoringGaps], "./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/".explode("_", array_keys($orderedTrimmedFinalVideo)[$keyIgnoringGaps])[1], $indexOfWordInFolder, (isset($markers[$keyMarkers+2]) ? $markers[$keyMarkers+2]: $markers[$keyMarkers+1]));
        } elseif ($indexOfWordInFolder < count($orderedTrimmedFinalVideo[array_keys($orderedTrimmedFinalVideo)[$keyIgnoringGaps]])) {
            echo "this is a directory\n";
            //we need to find the subwords that make up the main word
            $arrayOfSmallWordsToUse = build_bigword_from_smallwords($orderedTrimmedFinalVideo[array_keys($orderedTrimmedFinalVideo)[$keyIgnoringGaps]], explode(" ", $valueMarkers)[1]);
            echo("<pre>");
            echo " arrayOfSmallWordsToUse that was returned by the function \n";
            print_r($arrayOfSmallWordsToUse);
            echo("</pre>");
            if (empty($arrayOfSmallWordsToUse)) {
                echo "\nno subwords so using empty space generator\n";
                //call empty video generating function
                //$thisMarker, $nextMarker, $keyFromOrderedTrimmedVideoArray, $numberOfAlternativesToGenerateIndex, $nextNextMarker
                empty_space_generator($valueMarkers, $markers[$keyMarkers+1], array_keys($orderedTrimmedFinalVideo)[$keyIgnoringGaps], $indexOfWordInFolder, (isset($markers[$keyMarkers+2]) ? $markers[$keyMarkers+2]: $markers[$keyMarkers+1]));
                continue;
            }
            $arrayOfFilesThatMakeUpThisLyric = array();
            $arrayOfPathsThatMakeUpThisLyric = array();
            foreach ($arrayOfSmallWordsToUse as $keySmallWords => $valueSmallWords) {
                $keys = array_map('strlen', array_keys($lyricArrayShell1[$valueSmallWords]));
                array_multisort($keys, SORT_DESC, $lyricArrayShell1[$valueSmallWords]);
                echo("<pre>");
                echo " sorted array of subwords  \n";
                print_r($lyricArrayShell1[$valueSmallWords]);
                echo("</pre>");
                //first check if there is a numeric index and use that but if not une the top index coz its now the longest after the sort
                if (isset($lyricArrayShell1[$valueSmallWords][$indexOfWordInFolder])) {
                    $fileToSendToCutAndProcessLyricFunction = $lyricArrayShell1[$valueSmallWords][$indexOfWordInFolder];
                    $pathToLongClipForThisSubword = "$valueSmallWords";
                } elseif (isset($lyricArrayShell1[$valueSmallWords][0])) {
                    $fileToSendToCutAndProcessLyricFunction = $lyricArrayShell1[$valueSmallWords][0];
                    $pathToLongClipForThisSubword = "$valueSmallWords";
                } else {
                    reset($lyricArrayShell1[$valueSmallWords]);
                    $fileToSendToCutAndProcessLyricFunction = $lyricArrayShell1[$valueSmallWords][key($lyricArrayShell1[$valueSmallWords])][0];
                    $pathToLongClipForThisSubword = "$valueSmallWords/".key($lyricArrayShell1[$valueSmallWords]);
                }

                echo "file to add to array to send to function: $fileToSendToCutAndProcessLyricFunction";
                array_push($arrayOfFilesThatMakeUpThisLyric, $fileToSendToCutAndProcessLyricFunction);
                array_push($arrayOfPathsThatMakeUpThisLyric, "./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/".explode("_", array_keys($orderedTrimmedFinalVideo)[$keyIgnoringGaps])[1]."/$pathToLongClipForThisSubword");
            }
            //$files, $thisMarker, $nextMarker, $keyFromOrderedTrimmedVideoArray, $pathToLongClip, $numberOfAlternativesToGenerateIndex, $nextNextMarker
            cut_lyric_from_clip_and_add_slack_subwords($arrayOfFilesThatMakeUpThisLyric, $valueMarkers, $markers[$keyMarkers+1], array_keys($orderedTrimmedFinalVideo)[$keyIgnoringGaps], $arrayOfPathsThatMakeUpThisLyric, $indexOfWordInFolder, (isset($markers[$keyMarkers+2]) ? $markers[$keyMarkers+2]: $markers[$keyMarkers+1]));
        } else {
            echo "there is no index for the $indexOfWordInFolder element";
            empty_space_generator($valueMarkers, $markers[$keyMarkers+1], array_keys($orderedTrimmedFinalVideo)[$keyIgnoringGaps], $indexOfWordInFolder, (isset($markers[$keyMarkers+2]) ? $markers[$keyMarkers+2]: $markers[$keyMarkers+1]));
        }
    }
}








//---------------------------------------------------------------------------------------------
echo "end of bot video creation";
echo("</pre>");
