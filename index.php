<?php
require_once('functions.php');

if (!session_id()) {
    session_start();
}
exec('unset DYLD_LIBRARY_PATH ;');
putenv('DYLD_LIBRARY_PATH');
putenv('DYLD_LIBRARY_PATH=/usr/bin');
echo 'start time: '.  date("Y-m-d H:i:s");
set_time_limit(0);
error_reporting(E_ALL ^ E_NOTICE);
echo("<pre>");
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 50000);
$youtubedl = './youtube-dl';
$ffmpeg = './ffmpeg';
make_if_no_dir("./downloadedVideos");

//notes
//i think there should be some warninngs with simplexmil if the video does not have correct subtitles

//CAREFUL - DO NOT SET THE NUMBER OF TIMES VARS TOO HIGH OR IT WILL WRECK OUR FREE WATSON AND WREK STORAGE AND TAKE FOREVER JUST LEAVE IT LOW OK

//options - needed means what options need setting when in that number mode
$howAreWeGettingTheVideos = 1; //1 means from a whole yt channel, 2 means providing the videos from a txt, 3 means providng a youtube url with a CHANNEL query. I havent setup 3 to do normal queries (refer to youtube api for more info)
$numberOfTimesToGetSameWord = 3;//needed for all//HAS to be a single digit number or the bot will break ie 10 not allowed
$shouldSeparateFinalAudioAndVideo = false;//needed for all
$testingMode = false;//needed for all
$numberOfVideosToGetCaptionsFrom = 10;//needed for 1 and 3
$maxLengthOfVideoAllowed = 900;//needed for 1 and 3
$query = 'weekly address';//needed for 3, not used otherwise
$channelToGetVidsFrom = 'UC-lHJZR3Gqxm24_Vd_AJ5Yw';//needed for 3 and 1
$titleOfSong = 'GodsPlan';//needed for all **CHANGE SONG LYRICS - spaces removed so this is used to get txt file
$channelTitle = "PewDiePie";//needed for all - no spaces pls
$shouldAutotune = false; //needed for all
$amountToAddToEndAndSubtractFromStart = 1.0;//needed for all
$amountToAddToEndAndSubtractFromStartLongClips = 5;//needed for all
$shouldAutoGenerateFinalVideo = false;

$usernameWatson = 'your watson username';
$passwordWatson = 'your watson password';

if ($shouldAutotune) {
    $shouldSeparateFinalAudioAndVideo = true;
}
//eg only trumps weekly updates. It has to include the current query or it will break



$channelTitle = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $channelTitle);





//require "testing.php";
$titleOfSong = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $titleOfSong);
echo "title song " . $titleOfSong;
//this one is pewdiepie
//$channelToGetVidsFrom = 'UC-lHJZR3Gqxm24_Vd_AJ5Yw';
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';



//make a testing mode ie save array of pewds in json so we dont have the api call delay while testing

//---------------------------------------------------------------------------------------------
//lyrics of song
$songLyrics = file("./songLyrics/$titleOfSong.txt", FILE_IGNORE_NEW_LINES);

$explodedSongLyrics = array();
foreach ($songLyrics as $key => $value) {
    foreach (explode(" ", $value) as $keyExploded => $valueExploded) {
        if (!empty($valueExploded)) {
            // $valueExploded = str_replace(")", '', $valueExploded);
            // $valueExploded = str_replace(".", '', $valueExploded);
            // $valueExploded = str_replace("(", '', $valueExploded);
            // $valueExploded = str_replace(",", '', $valueExploded);
            // $valueExploded = str_replace("*", '', $valueExploded);
            $valueExploded = str_replace("'", '', $valueExploded);
            $valueExploded = preg_replace("/[^A-Za-z0-9 ]/", "", $valueExploded);
            array_push($explodedSongLyrics, strtolower($valueExploded));
        }
    }
}
echo("<pre>");
echo " non unique lyrics  \n";
print_r($explodedSongLyrics);
echo("</pre>");
$explodedSongLyrics = array_unique($explodedSongLyrics);
//now we have to make each lyric appear $numberOfTimesToGetSameWord times
$multipliedExplodedSongLyrics =array();
for ($i=0; $i < $numberOfTimesToGetSameWord; $i++) {
    $iplusone = $i + 1;
    foreach ($explodedSongLyrics as $key => $value) {
        array_push($multipliedExplodedSongLyrics, $value."_".$iplusone);
    }
}
//doing this coz i have $explodedSongLyrics written everywhere
$explodedSongLyrics = $multipliedExplodedSongLyrics;
echo("<pre>");
echo " exploded lyrics  \n";
print_r($explodedSongLyrics);
echo("</pre>");
//---------------------------------------------------------------------------------------------
//get video transcripts
if (!$testingMode) {
    if ($howAreWeGettingTheVideos == 1 || $howAreWeGettingTheVideos == 3) {
        require 'youtubeDataGetter.php';
    } elseif ($howAreWeGettingTheVideos == 2) {

    //parse the txt actual video urls to get the id

        $arrayOfChannelVideoIDs = array();
        $videoURLs = file("videoURLsToUse.txt", FILE_IGNORE_NEW_LINES);
        foreach ($videoURLs as $key => $value) {
            $videoID = explode('?v=', explode('/', $value)[3])[1];
            array_push($arrayOfChannelVideoIDs, $videoID);
        }
    }
} else {
    $toDecode = file_get_contents('temparray.txt');
    $decodedArray = json_decode($toDecode, true);
    $arrayWithYoutubeTranscript =$decodedArray;
}

if (!$testingMode) {
    $arrayWithYoutubeTranscript = array();
    foreach ($arrayOfChannelVideoIDs as $key => $value) {
        $xml = simplexml_load_file("http://video.google.com/timedtext?lang=en&v=$value");
        $arrayWithYoutubeTranscript[$value] = array();
        foreach ($xml->text as $key1 => $value1) {
            //$arrayWithYoutubeTranscript[$value] = (array)$value1;
            array_push($arrayWithYoutubeTranscript[$value], (array)$value1);
        }
    }
    foreach ($arrayWithYoutubeTranscript as $key => $value) {
        if (empty($value)) {
            unset($arrayWithYoutubeTranscript[$key]);
        }
    }
    echo count($arrayWithYoutubeTranscript)/count($arrayOfChannelVideoIDs) . ' is the percentage of videos with valid xml subs';
    if (count($arrayWithYoutubeTranscript) < 0.25*count($arrayOfChannelVideoIDs)) {
        echo 'fewer than 25 percent of human inserted subtitles were found on videos on this channel so we are now trying auto genned subs';
        require "getAutoGenSubs.php";
    }


    $encoded = json_encode($arrayWithYoutubeTranscript, JSON_FORCE_OBJECT);
    file_put_contents("temparray.txt", $encoded);
}
function shuffle_assoc($list)
{
    $keys = array_keys($list);
    shuffle($keys);
    $random = array();
    foreach ($keys as $key) {
        $random[$key] = $list[$key];
    }
    return $random;
}

echo("<pre>");
echo 'array of youtube transcript';
print_r($arrayWithYoutubeTranscript);
echo("</pre>");
$arrayWithYoutubeTranscript = shuffle_assoc($arrayWithYoutubeTranscript);

echo 'array of youtube transcript after shuffle';
print_r($arrayWithYoutubeTranscript);
echo("</pre>");

foreach ($arrayWithYoutubeTranscript as $id => $arrayOfCaptions) {
    foreach ($arrayOfCaptions as $keyCaptionPart => $captionPart) {
        $arrayWithYoutubeTranscript[$id][$keyCaptionPart][0] = ' '.$captionPart[0].' ';
    }
}

//---------------------------------------------------------------------------------------------
//this array will be used to keep track of all captions used for all words
$arrayOfCaptionsFoundForLyricRegardlessOfIndex = array();
foreach ($explodedSongLyrics as $lyric) {
    $arrayOfCaptionsFoundForLyricRegardlessOfIndex[substr($lyric, 0, -2)] = array();
}

//---------------------------------------------------------------------------------------------
//match lyrics with transcript
$arrayOfMatches = array();
$lyricsFoundAlready = array();

for ($i=0; $i < count($explodedSongLyrics); $i++) {
    //we have this for loop to make it more thorough
    $arrayWithYoutubeTranscript = shuffle_assoc($arrayWithYoutubeTranscript);
    foreach ($arrayWithYoutubeTranscript as $id => $arrayOfCaptions) {
        foreach ($arrayOfCaptions as $captionPart) {
            if (stripos($captionPart[0], ':') !== false || stripos($captionPart[0], 'quot') !== false) {
                //if we found multiple people in the video (because the name felix is given when he speaks), then only allow speech that has the word felix in it
                //in fact, so it isnt pewds specific, make it so that if it finds a colon in the caption, it skips it, coz colons indicate multiple characters
                //quot means the audio is from the vidoe
                continue;
            }
            $captionPart[0] = preg_replace("/[^A-Za-z0-9 ]/", "", $captionPart[0]);
            $captionPart[0] = str_replace("39", '', $captionPart[0]);
            foreach ($explodedSongLyrics as $lyric) {

        //now we need substr for all loops like this
                if (stripos($captionPart[0], ' '.substr($lyric, 0, -2).' ') !== false &&  !in_array($lyric, $lyricsFoundAlready)) {
                    foreach ($arrayOfCaptionsFoundForLyricRegardlessOfIndex[substr($lyric, 0, -2)] as $captionAlreadyUsedForThisLyricRegardlessOfIndex) {
                        if ($captionAlreadyUsedForThisLyricRegardlessOfIndex == $captionPart[0]) {
                            continue 4;
                        }
                    }
                    array_push($arrayOfMatches, array('id' => $id,'lyric' => $lyric, $captionPart));
                    array_push($lyricsFoundAlready, $lyric);
                    array_push($arrayOfCaptionsFoundForLyricRegardlessOfIndex[substr($lyric, 0, -2)], $captionPart[0]);
                }
            }
        }
    }
}
$arrayWithYoutubeTranscript = shuffle_assoc($arrayWithYoutubeTranscript);
//in this first matching stage we do it twice coz the above one goes to a whole new video after it finds a lyric to try and minimise all the words coming from one video. Now we need to fill in the blanks by doing it properly
foreach ($arrayWithYoutubeTranscript as $id => $arrayOfCaptions) {
    foreach ($arrayOfCaptions as $captionPart) {
        if (stripos($captionPart[0], ':') !== false || stripos($captionPart[0], 'quot') !== false) {
            //if we found multiple people in the video (because the name felix is given when he speaks), then only allow speech that has the word felix in it
            //in fact, so it isnt pewds specific, make it so that if it finds a colon in the caption, it skips it, coz colons indicate multiple characters
            //quot means the audio is from the vidoe
            continue;
        }
        $captionPart[0] = preg_replace("/[^A-Za-z0-9 ]/", "", $captionPart[0]);
        $captionPart[0] = str_replace("39", '', $captionPart[0]);
        foreach ($explodedSongLyrics as $lyric) {

        //now we need substr for all loops like this
            if (stripos($captionPart[0], ' '.substr($lyric, 0, -2).' ') !== false &&  !in_array($lyric, $lyricsFoundAlready)) {
                foreach ($arrayOfCaptionsFoundForLyricRegardlessOfIndex[substr($lyric, 0, -2)] as $captionAlreadyUsedForThisLyricRegardlessOfIndex) {
                    if ($captionAlreadyUsedForThisLyricRegardlessOfIndex == $captionPart[0]) {
                        continue 2;
                    }
                }
                array_push($arrayOfMatches, array('id' => $id,'lyric' => $lyric, $captionPart));
                array_push($lyricsFoundAlready, $lyric);
                array_push($arrayOfCaptionsFoundForLyricRegardlessOfIndex[substr($lyric, 0, -2)], $captionPart[0]);
            }
        }
    }
}

echo("<pre>");
echo "  lyrics found already \n";
print_r($lyricsFoundAlready);
echo("</pre>");

$lyricsNotFound = array_diff($explodedSongLyrics, $lyricsFoundAlready);
$percentageOfLyricsNotFound = ((count($explodedSongLyrics)-count($lyricsFoundAlready))/count($explodedSongLyrics))*100;
$percentageOfLyricsNotFound = (count($lyricsNotFound)/count($explodedSongLyrics))*100;//same thing as above
echo("<pre>");

echo "  lyrics not found \n";
print_r($lyricsNotFound);
echo 'percentage of lyrics not found:';
print($percentageOfLyricsNotFound ."%");
echo("</pre>");

// echo ("<pre>");
// echo " array of matches:  \n";
// print_r($arrayOfMatches);
// echo ("</pre>");

//---------------------------------------------------------------------------------------------
//try to find alternatives for lyrics not found
//i should make it transfer lyrics to this section if the werent found $numberOfTimesToGetSameWord in the previous section
$arrayWithYoutubeTranscript = shuffle_assoc($arrayWithYoutubeTranscript);

$arrayOfAlternativeMatches = array();
$lyricsFinallyFoundAfterAlternatives = array();

foreach ($arrayWithYoutubeTranscript as $id => $arrayOfCaptions) {
    foreach ($arrayOfCaptions as $captionPart) {
        if (stripos($captionPart[0], ':') !== false || stripos($captionPart[0], 'quot') !== false) {
            //if we found multiple people in the video (because the name felix is given when he speaks), then only allow speech that has the word felix in it
            //in fact, so it isnt pewds specific, make it so that if it finds a colon in the caption, it skips it, coz colons indicate multiple characters
            continue;
        }
        $captionPart[0] = preg_replace("/[^A-Za-z0-9 ]/", "", $captionPart[0]);
        $captionPart[0] = str_replace("39", '', $captionPart[0]);
        foreach ($lyricsNotFound as $lyric) {
            if (stripos($captionPart[0], substr($lyric, 0, -2)) !== false && !in_array($lyric, $lyricsFinallyFoundAfterAlternatives)) {
                foreach ($arrayOfCaptionsFoundForLyricRegardlessOfIndex[substr($lyric, 0, -2)] as $captionAlreadyUsedForThisLyricRegardlessOfIndex) {
                    if ($captionAlreadyUsedForThisLyricRegardlessOfIndex == $captionPart[0]) {
                        continue 2;
                    }
                }
                //im thinking we need to check the previous arary of captions found for lyrics to make sure that we dont just use the same caption..yeah we need it to check back every stage
                //how about we just have a nice massive array that contains all the captions 'used up' insead of these individual loops for each filter stage
                //wait a minute...do we even need a separate array for this for each stage? Cant it constantly put it into the same array??

                $explodedString = explode(" ", $captionPart[0]);

                foreach ($explodedString as $valueString) {
                    if (stripos($valueString, substr($lyric, 0, -2)) !== false) {
                        $actualWord = $valueString;
                    }
                }

                array_push($arrayOfAlternativeMatches, array('id' => $id,'lyric' => $lyric,'actual_word' => $actualWord, $captionPart));
                array_push($lyricsFinallyFoundAfterAlternatives, $lyric);
                array_push($arrayOfCaptionsFoundForLyricRegardlessOfIndex[substr($lyric, 0, -2)], $captionPart[0]);
            }
        }
    }
}


echo("<pre>");
echo " lyrics found after alternatives  \n";
print_r($lyricsFinallyFoundAfterAlternatives);
echo("</pre>");
$lyricsStillNotFound = array_diff($lyricsNotFound, $lyricsFinallyFoundAfterAlternatives);
$percentageOfLyricsStillNotFound = (count($lyricsStillNotFound)/count($explodedSongLyrics))*100;

echo("<pre>");
echo " lyrics still not found:  \n";
print_r($lyricsStillNotFound);
echo 'percentage of lyrics STILL not found:';
print_r($percentageOfLyricsStillNotFound."%");
echo("</pre>");
// echo ("<pre>");
// echo " array of alternative matches: \n";
// print_r($arrayOfAlternativeMatches);
// echo ("</pre>");

//---------------------------------------------------------------------------------------------
//do a dictionary search to break up the remaining words not found as in the lyrics can be part of words in captions
$arrayWithYoutubeTranscript = shuffle_assoc($arrayWithYoutubeTranscript);

$dictionary = file('words_alpha.txt', FILE_IGNORE_NEW_LINES);
$arrayOfSubWordsFoundFromDictionary = array();
foreach ($lyricsStillNotFound as $valuelyricsStillNotFound) {
    $arrayOfSubWordsFoundFromDictionary[$valuelyricsStillNotFound] = array();
    foreach ($dictionary as $wordInDictionary) {
        if (stripos($valuelyricsStillNotFound, $wordInDictionary) !== false && substr($valuelyricsStillNotFound, 0, -2) != $wordInDictionary && strlen($wordInDictionary) >= 3) {
            array_push($arrayOfSubWordsFoundFromDictionary[$valuelyricsStillNotFound], $wordInDictionary.substr($valuelyricsStillNotFound, -2));
        }
    }
    //trying this so it will add the original word so that it can find similar sounding words to the original
    array_unshift($arrayOfSubWordsFoundFromDictionary[$valuelyricsStillNotFound], $valuelyricsStillNotFound);
}
foreach ($arrayOfSubWordsFoundFromDictionary as $keyMainLyric => $valuelyric) {
    foreach ($valuelyric as $subWord) {
        $arrayOfCaptionsFoundForLyricRegardlessOfIndex[substr($keyMainLyric, 0, -2)][substr($subWord, 0, -2)] = array();
    }
}
// echo ("<pre>");
// echo "  array of subwords found from dictionary \n";
// print_r($arrayOfSubWordsFoundFromDictionary);
// echo ("</pre>");
//remembre to reconstruct the whol word from the sub parts to save in the final file
//find these dictionary found sub words in subtitle transcript
$arrayOfSubwordMatches = array();
$subwordsFoundInCaptions = array();
foreach ($arrayWithYoutubeTranscript as $id => $arrayOfCaptions) {
    foreach ($arrayOfCaptions as $captionPart) {
        //ugh its annoying we cant put this normalising bit in a function coz u cant contiue from the function
        if (stripos($captionPart[0], ':') !== false || stripos($captionPart[0], 'quot') !== false) {
            //if we found multiple people in the video (because the name felix is given when he speaks), then only allow speech that has the word felix in it
            //in fact, so it isnt pewds specific, make it so that if it finds a colon in the caption, it skips it, coz colons indicate multiple characters
            continue;
        }
        $captionPart[0] = preg_replace("/[^A-Za-z0-9 ]/", "", $captionPart[0]);
        $captionPart[0] = str_replace("39", '', $captionPart[0]);
        foreach ($arrayOfSubWordsFoundFromDictionary as $keyMainLyric => $valuelyric) {
            foreach ($valuelyric as $subWord) {
                if (stripos($captionPart[0], ' '.substr($subWord, 0, -2).' ') !== false && !in_array($subWord, $subwordsFoundInCaptions)) {
                    foreach ($arrayOfCaptionsFoundForLyricRegardlessOfIndex[substr($keyMainLyric, 0, -2)][substr($subWord, 0, -2)] as $captionAlreadyUsedForThisLyricRegardlessOfIndex) {
                        if ($captionAlreadyUsedForThisLyricRegardlessOfIndex == $captionPart[0]) {
                            continue 2;
                        }
                    }

                    array_push($arrayOfSubwordMatches, array('main_lyric'=>$keyMainLyric ,'id' => $id,'lyric' => $subWord, $captionPart));
                    array_push($subwordsFoundInCaptions, $subWord);
                    array_push($arrayOfCaptionsFoundForLyricRegardlessOfIndex[substr($keyMainLyric, 0, -2)][substr($subWord, 0, -2)], $captionPart[0]);
                }
            }
        }
    }
}
//
// echo ("<pre>");
// echo "  arrayOfCaptionsFoundForLyricRegardlessOfIndex \n";
// print_r($arrayOfCaptionsFoundForLyricRegardlessOfIndex);
// echo ("</pre>");


// echo ("<pre>");
// echo " subwords found in captions  \n";
// print_r($subwordsFoundInCaptions);
// echo ("</pre>");
$lyricsStillNotFoundEvenAfterSubwords = array_diff($lyricsStillNotFound, $subwordsFoundInCaptions);
//this wont really make sense itll give the same value. remember, its subwords we're finding, not entire lyrics, so uncertainty is the same
$percentageOfLyricsStillNotFoundEvenAfterSubwords = (count($lyricsStillNotFoundEvenAfterSubwords)/count($explodedSongLyrics))*100;

// echo ("<pre>");
// echo " lyrics still not found: (wont make sense - check script comments) \n";
// print_r($lyricsStillNotFoundEvenAfterSubwords);
// echo 'percentage of lyrics STILL  not found:';
// print_r($percentageOfLyricsStillNotFoundEvenAfterSubwords."%");
// echo ("</pre>");
// echo ("<pre>");
// echo " array of subword matches: \n";
// print_r($arrayOfSubwordMatches);
// echo ("</pre>");

//now reform the original subwords array but only using the words found in captions
$arrayOfSubWordsFoundFromDictionaryThatAreInCaptions = array();
foreach ($arrayOfSubWordsFoundFromDictionary as $keyMainLyric => $valuelyric) {
    $arrayOfSubWordsFoundFromDictionaryThatAreInCaptions[$keyMainLyric] = array();

    foreach ($valuelyric as $subWord) {
        foreach ($arrayOfSubwordMatches as $valueSubwordMatches) {
            if ($keyMainLyric == $valueSubwordMatches['main_lyric'] && $subWord == $valueSubwordMatches['lyric']) {
                array_push($arrayOfSubWordsFoundFromDictionaryThatAreInCaptions[$keyMainLyric], $subWord);
            }
        }
    }
}
// echo ("<pre>");
// echo "  new array subwords found from dict that are actually in captions \n";
// print_r($arrayOfSubWordsFoundFromDictionaryThatAreInCaptions);
// echo ("</pre>");

//interim break---------------------------------------------------------------------------------------------
//now check that the main lyric can be formed from these possible subwords and if not, push them through to the next stage to get similar sounding words. maybe we should make this above block a function since were gonna have to use it for the similar sounding words lol.
//lets classify the lyrics that cant be formed if they have less than 2 subwords possible to them AND the subword is more than 1 character less than the full lyric word
$arrayWithYoutubeTranscript = shuffle_assoc($arrayWithYoutubeTranscript);

$lyricsStillNotFoundBecauseNoSubwords = array();
foreach ($arrayOfSubWordsFoundFromDictionaryThatAreInCaptions as $keyMainLyric => $valuelyric) {
    if (empty($valuelyric)) {
        array_push($lyricsStillNotFoundBecauseNoSubwords, $keyMainLyric);
        unset($arrayOfSubWordsFoundFromDictionaryThatAreInCaptions[$keyMainLyric]);
    }
    foreach ($valuelyric as $keySubword => $actuallyPlausibleSubword) {
        if (count($valuelyric) < 2) {
            //if there are only (now less than) 2 lyrics and theyre both too short ie most likely wont be able to fully form the main lyric, basically dont count it
            if (strlen(substr($actuallyPlausibleSubword, 0, -2)) < (strlen(substr($keyMainLyric, 0, -2))-1)) {
                echo 'basically not counting it';

                array_push($lyricsStillNotFoundBecauseNoSubwords, $keyMainLyric);
                //$lyricsStillNotFoundBecauseNoSubwords means because no VALID subwords that are actually in the captions
                unset($arrayOfSubWordsFoundFromDictionaryThatAreInCaptions[$keyMainLyric]);
            }
        }
    }
}

//now go back to $subwordsFoundInCaptions and remove any values that dont comply with the above classification by scanning which main lyrics are still keys of the $arrayOfSubwordMatches after some have been unset
$arrayWithMainLyricsThatPassClassification = array();
foreach ($arrayOfSubWordsFoundFromDictionaryThatAreInCaptions as $keyMainLyric => $valueArrayOfSubLyrics) {
    array_push($arrayWithMainLyricsThatPassClassification, $keyMainLyric);
}

foreach ($arrayOfSubwordMatches as $key => $value) {
    if (!in_array($value['main_lyric'], $arrayWithMainLyricsThatPassClassification)) {
        //it wont unset the same number of unset $arrayOfSubWordsFoundFromDictionaryThatAreInCaptions in the code above because already the empty arrays arent part of $arrayOfSubwordMatches...its kinda the reason why theyre empty in the first place lemao
        unset($arrayOfSubwordMatches[$key]);
        //ohhhh lmao it does actually unset it it just keeps the keys the same so the last key is likely to be the same as the last key in the array before unsetting process
    }
}


$percentageOfLyricsStillNotFoundEvenAfterSubwordsBecauseNoSubwords = (count($lyricsStillNotFoundBecauseNoSubwords)/count($explodedSongLyrics))*100;

// echo ("<pre>");
// echo " lyrics stillllll not found:  \n";
// print_r($lyricsStillNotFoundBecauseNoSubwords);
// echo 'percentage of lyrics STILL STILL not found coz no subwords:';
// print_r($percentageOfLyricsStillNotFoundEvenAfterSubwordsBecauseNoSubwords."%");
// echo ("</pre>");
// echo ("<pre>");
// // echo " array of subword that are in captions: \n";
// // print_r($arrayOfSubWordsFoundFromDictionaryThatAreInCaptions);
// // echo ("</pre>");
// echo " array of subword matches after classification: \n";
// print_r($arrayOfSubwordMatches);
// echo ("</pre>");

//now we need the array with all the info like attribtes and main lyric and lyric etc basically $arrayOfSubwordMatches but wit the bad ones remoed

//---------------------------------------------------------------------------------------------
//use dat api to get dem similar sounding words
//hmm...the main lyrics dont seem to come with anything useful...basically all the words returned are weird words that the youtuber would never use so they would fail the appearing in caption test...how about we use the sub lyrics to see if there are any similar sounding words
$arrayWithYoutubeTranscript = shuffle_assoc($arrayWithYoutubeTranscript);

//we should inject the original words into $lyricsStillNotFoundBecauseNoSubwords to try and find similar sounding words. ah, we have to inject, because the original word acting as a sub caption will simply not be found in any captions, otherwise it wouldn't be here in the first place! inject, or at least make an exceptino if its the original lyric, as done in the in_array below. acc i think its best to just inject, that doesn't seem to be working.
//WE ASSUME that the original lyric was not found in a caption because otherwise it wouldnt go to subwords, it would have been found in the caption before

foreach ($arrayOfSubWordsFoundFromDictionary as $key => $value) {
    if (in_array($key, $value)) {
        array_push($lyricsStillNotFoundBecauseNoSubwords, $key);
    }
}
echo " lyrics stillllll not found AFTER INJECTION OF MAIN LYRIC:  \n";
print_r($lyricsStillNotFoundBecauseNoSubwords);

$arrayOfSimilarSoundingWords = array();
foreach ($arrayOfSubWordsFoundFromDictionary as $keyMainLyric => $valueArrayOfSubwords) {
    foreach ($lyricsStillNotFoundBecauseNoSubwords as $valueMainLyric) {
        //this in array is the equivalent of injecting. At least to the same effect. oh lmao it wont work coz this is a loop for only lyrics still not found whic the main lyric wont be in, so we do need to inject it
        if ($valueMainLyric == $keyMainLyric) {
            //up until this point its basically doing the same as just looping through all the not found lyrics (because now we unset bad ones duhh)
            foreach ($valueArrayOfSubwords as $valueSubWord) {
                $substrValueSubWord = substr($valueSubWord, 0, -2);

                $arrayOfSimilarSoundingWords[$keyMainLyric][$valueSubWord] = array();
                $similarSoundingWordsToSubWords = file_get_contents("https://api.datamuse.com/words?sl=$substrValueSubWord");
                $similarSoundingWordsToSubWords =json_decode($similarSoundingWordsToSubWords, true);
                foreach ($similarSoundingWordsToSubWords as $valueSimilarWord) {
                    //fuck it lets try and match every single result the api returns lemao computers are so fast these darn days
                    $word = $valueSimilarWord['word'];
                    if (strrpos($word, " ") !== false) {
                        //if there is a space in the word:
                        $wordsArray = explode(" ", $word);
                        $smallestLengthYet = 0;
                        foreach ($wordsArray as $valueSplitWords) {
                            if (strlen($valueSplitWords) >= $smallestLengthYet) {
                                $word = $valueSplitWords;
                            }
                        }
                    }
                    array_push($arrayOfSimilarSoundingWords[$keyMainLyric][$valueSubWord], array('word' => $word,'score' => $valueSimilarWord['score']));
                }
            }
        }
    }
}
//
// echo ("<pre>");
// //turn this echo off when done testing
// echo "  array of similar sounding words \n";
// print_r($arrayOfSimilarSoundingWords);
// echo ("</pre>");



foreach ($arrayOfSimilarSoundingWords as $keyMainLyric => $valueArrayOfSubwords) {
    foreach ($valueArrayOfSubwords as $keySubword => $valueArrayOfSimilarSoundingToSubwords) {
        foreach ($valueArrayOfSimilarSoundingToSubwords as $valueSubWordAndScore) {
            //im thinking this shouldnt be regardless of index - nope , I have no idea what i'm doing. rip.
            $arrayOfCaptionsFoundForLyricRegardlessOfIndex[substr($keyMainLyric, 0, -2)][substr($keySubword, 0, -2)][$valueSubWordAndScore['word']] = array();
        }
    }
}
// echo ("<pre>");
// //at this stage, this array is not meant to have any of the similar sounding words populated
// echo " arrayOfCaptionsFoundForLyricRegardlessOfIndex  \n";
// print_r($arrayOfCaptionsFoundForLyricRegardlessOfIndex);
// echo ("</pre>");

$similarSoundingWordsFoundInCaptions = array();
$arrayOfSimilarSoundingWordsMatches = array();
$arrayOfMainLyricsFoundInSimilarSoundingProcess = array();

foreach ($arrayWithYoutubeTranscript as $id => $arrayOfCaptions) {
    foreach ($arrayOfCaptions as $captionPart) {
        if (stripos($captionPart[0], ':') !== false || stripos($captionPart[0], 'quot') !== false) {
            //if we found multiple people in the video (because the name felix is given when he speaks), then only allow speech that has the word felix in it
            //in fact, so it isnt pewds specific, make it so that if it finds a colon in the caption, it skips it, coz colons indicate multiple characters
            continue;
        }
        $captionPart[0] = preg_replace("/[^A-Za-z0-9 ]/", "", $captionPart[0]);
        $captionPart[0] = str_replace("39", '', $captionPart[0]);
        foreach ($arrayOfSimilarSoundingWords as $keyMainLyric => $valuelyric) {
            foreach ($valuelyric as $keySubWord => $subWordArray) {
                foreach ($subWordArray as $similarSoundingWord) {
                    if (stripos($captionPart[0], ' '.$similarSoundingWord['word'].' ') !== false && !in_array($similarSoundingWord['word'].substr($keySubWord, -2), $similarSoundingWordsFoundInCaptions)) {
                        foreach ($arrayOfCaptionsFoundForLyricRegardlessOfIndex[substr($keyMainLyric, 0, -2)][substr($keySubWord, 0, -2)][$similarSoundingWord['word']] as $captionAlreadyUsedForThisLyricRegardlessOfIndex) {
                            if ($captionAlreadyUsedForThisLyricRegardlessOfIndex == $captionPart[0]) {
                                continue 2;
                            }
                        }

                        array_push($arrayOfSimilarSoundingWordsMatches, array('main_lyric'=>$keyMainLyric,'sub_lyric' => $keySubWord,'id' => $id,'similar_lyric' => $similarSoundingWord['word'], 'score' => $similarSoundingWord['score'], $captionPart));
                        array_push($similarSoundingWordsFoundInCaptions, $similarSoundingWord['word'].substr($keySubWord, -2));
                        array_push($arrayOfCaptionsFoundForLyricRegardlessOfIndex[substr($keyMainLyric, 0, -2)][substr($keySubWord, 0, -2)][$similarSoundingWord['word']], $captionPart[0]);
                        array_push($arrayOfMainLyricsFoundInSimilarSoundingProcess, $keyMainLyric);
                    }
                }
            }
        }
    }
}
// echo ("<pre>");
// echo "  found array \n";
// print_r($arrayOfCaptionsFoundForLyricRegardlessOfIndex);
// echo ("</pre>");

$lyricsStillNotFoundEvenAfterSubwordsAndSimilarWords = array_diff($lyricsStillNotFoundBecauseNoSubwords, $arrayOfMainLyricsFoundInSimilarSoundingProcess);

$percentageOfLyricsStillNotFoundEvenAfterSubwordsBecauseNoSubwordsAndUsingSimilarWords = (count($lyricsStillNotFoundEvenAfterSubwordsAndSimilarWords)/count($explodedSongLyrics))*100;

// echo ("<pre>");
// echo ("<pre>");
// echo " lyrics still not found EvenAfterSubwordsAndSimilarWords:  \n";
// print_r($lyricsStillNotFoundEvenAfterSubwordsAndSimilarWords);
// echo 'percentage of lyrics STILL STILL STILLLl not found after similar word api search (which is sad coz this is the last filter:';
// print_r($percentageOfLyricsStillNotFoundEvenAfterSubwordsBecauseNoSubwordsAndUsingSimilarWords."%");
// echo ("</pre>");
// echo ("<pre>");
//
// // echo ("<pre>");
// echo "  array of similar sounding words matches \n";
// print_r($arrayOfSimilarSoundingWordsMatches);
// echo ("</pre>");
//and FINALLY we have reached the stage of no lyrics not found. Bit OTT to get here tbh. Now we gotta choose the highest scoring similar sounding word.

//---------------------------------------------------------------------------------------------
//we have to get all occurances of (*already proven as valid*) sub lyrics in $arrayOfSimilarSoundingWordsMatches and then find the highest score for our final result
//first lets group together all the same sub lyrics
$arrayWithYoutubeTranscript = shuffle_assoc($arrayWithYoutubeTranscript);

$arrayOfGroupedSubLyricsToCompareScores = array();
foreach ($arrayOfSubWordsFoundFromDictionary as $keyMainLyric => $valueArrayOfSubwords) {
    foreach ($valueArrayOfSubwords as $valueSubWord) {
        $arrayOfGroupedSubLyricsToCompareScores[$valueSubWord]=array();
        foreach ($arrayOfSimilarSoundingWordsMatches as $key => $value) {
            if ($valueSubWord == $value['sub_lyric']) {
                array_push($arrayOfGroupedSubLyricsToCompareScores[$valueSubWord], array($value['similar_lyric'], $value['score']));
            }
        }
    }
}
//&& $value['sub_lyric'] != substr($keyMainLyric,0,-2) && strlen($value['similar_lyric'] > 1)
//this is to remove similar sub lyrics that are only one letter long since the above note did not work.
foreach ($arrayOfGroupedSubLyricsToCompareScores as $keySubWord => $valueArrayOfSimilarSubWords) {
    foreach ($valueArrayOfSimilarSubWords as $key => $valueScoreAndSimilarLyricArray) {
        if (strlen($valueScoreAndSimilarLyricArray[0]) <2) {
            unset($arrayOfGroupedSubLyricsToCompareScores[$keySubWord][$key]);
        }
        if ($valueScoreAndSimilarLyricArray[0] == substr($keySubWord, 0, -2)) {
            unset($arrayOfGroupedSubLyricsToCompareScores[$keySubWord][$key]);
        }
    }
}
// echo ("<pre>");
// echo " array of grouped sub lyrics  \n";
// print_r($arrayOfGroupedSubLyricsToCompareScores);
// echo ("</pre>");
$arrayWithSubLyricAndSimilarSubLyricBasedOnScore = array();
$arrayWithSimilarSoundingWordsRegardlessOfScore = array();


foreach ($arrayOfGroupedSubLyricsToCompareScores as $keySubLyric => $valueArrayOfSimilarSubWords) {
    if (empty($valueArrayOfSimilarSubWords)) {
        continue;
    }

    //now sort same confidence elements based on how close their length is to the original subword
    //fuck thats way too hard, sort based on strlen, longer string preferred since it can be cut down.
    //maybe if we do the length sorting first, then after the score sorting, it'll keep both orders, with score being more important? ayyy it worked....oh ...maybe not...well kinda...i think it does help a bit...no it does jack all mate... how about we loop through and add an array element tooesahosntuhsaoutblah blah fuck it cba

    //
    usort($valueArrayOfSimilarSubWords, function ($a, $b) {
        return strlen($a[0]) - strlen($b[0]);
    });
    $valueArrayOfSimilarSubWords = array_reverse($valueArrayOfSimilarSubWords);


    // sort based on confidence
    usort($valueArrayOfSimilarSubWords, function ($a, $b) {
        return $a[1] - $b[1];
    });
    $valueArrayOfSimilarSubWords = array_reverse($valueArrayOfSimilarSubWords);

    $arrayOfGroupedSubLyricsToCompareScores[$keySubLyric] =$valueArrayOfSimilarSubWords;

    if (!array_key_exists(substr($keySubLyric, 0, -2), $arrayWithSimilarSoundingWordsRegardlessOfScore)) {
        $arrayWithSimilarSoundingWordsRegardlessOfScore[substr($keySubLyric, 0, -2)] = array();
    }


    for ($i=0; $i < $numberOfTimesToGetSameWord; $i++) {
        if (!in_array($valueArrayOfSimilarSubWords[$i][0], $arrayWithSimilarSoundingWordsRegardlessOfScore[substr($keySubLyric, 0, -2)]) && !empty($valueArrayOfSimilarSubWords[$i][0])) {
            $arrayWithSubLyricAndSimilarSubLyricBasedOnScore[$keySubLyric] = $valueArrayOfSimilarSubWords[$i][0].substr($keySubLyric, -2);

            array_push($arrayWithSimilarSoundingWordsRegardlessOfScore[substr($keySubLyric, 0, -2)], $valueArrayOfSimilarSubWords[$i][0]);
            break;
        }
        // if not in array but there aren't any other words left, then go for the repeat because the caption is different anyway
        elseif (in_array($valueArrayOfSimilarSubWords[$i][0], $arrayWithSimilarSoundingWordsRegardlessOfScore[substr($keySubLyric, 0, -2)]) && !isset($valueArrayOfSimilarSubWords[$i+1][0]) && !empty($valueArrayOfSimilarSubWords[$i][0])) {
            $arrayWithSubLyricAndSimilarSubLyricBasedOnScore[$keySubLyric] = $valueArrayOfSimilarSubWords[$i][0].substr($keySubLyric, -2);

            array_push($arrayWithSimilarSoundingWordsRegardlessOfScore[substr($keySubLyric, 0, -2)], $valueArrayOfSimilarSubWords[$i][0]);
            break;
        }
    }
    // echo ("<pre>");
// echo " arrayWithSimilarSoundingWordsRegardlessOfScore  \n";
// print_r($arrayWithSimilarSoundingWordsRegardlessOfScore);
// echo ("</pre>");
//
}
//
// echo ("<pre>");
// echo " array of grouped sub lyrics after sorting  \n";
// print_r($arrayOfGroupedSubLyricsToCompareScores);
// echo ("</pre>");
//
// echo ("<pre>");
// echo " array of sub lyric and highest rated similar sub lyric \n";
// print_r($arrayWithSubLyricAndSimilarSubLyricBasedOnScore);
// echo ("</pre>");

//its fine if there is the same similar sub lyric because there will be different captions
//Copyright Ethan Sarif-Kattan 2018
//---------------------------------------------------------------------------------------------
//we should make a nice array so its easier to send the watson requests for the words made of sub words made of sub lyrics.
//filter order: we have words directly from captions, then we have words that are parts of words from captions, then we have words that are a part of words that we could not find, then we have similar sounding words from the previous step but
$arrayOfValidSimilarSoundingWordMatches = array();
foreach ($arrayOfSimilarSoundingWordsMatches as $key => $value) {
    foreach ($arrayWithSubLyricAndSimilarSubLyricBasedOnScore as $keySubLyric => $valueSubSimilarLyric) {
        if ($value['sub_lyric'] == $keySubLyric && $value['similar_lyric'] == substr($valueSubSimilarLyric, 0, -2)) {
            $value['similar_lyric'] =$valueSubSimilarLyric;

            array_push($arrayOfValidSimilarSoundingWordMatches, $value);
        }
    }
}

echo("<pre>");
echo " new array of similar sounding words matches \n";
print_r($arrayOfValidSimilarSoundingWordMatches);
echo("</pre>");

// echo ("<pre>");
// echo "  arrayOfCaptionsFoundForLyricRegardlessOfIndex \n";
// print_r($arrayOfCaptionsFoundForLyricRegardlessOfIndex);
// echo ("</pre>");
echo 'end of first bit of index';
//---------------------------------------------------------------------------------------------
//youtube download
require "youtubeDownload.php";

//---------------------------------------------------------------------------------------------
//watson for speech to text for keyword recognition and timings and place in dir
require "watson.php";

//---------------------------------------------------------------------------------------------
//now we shuold make a new dir for all the lyrics in order of that in the song including repeats so it is easy to drag in for editing
$explodedSongLyricsOrderedAndNonUnique = array();
foreach ($songLyrics as $key => $value) {
    foreach (explode(" ", $value) as $keyExploded => $valueExploded) {
        if (!empty($valueExploded)) {
            // $valueExploded = str_replace(")", '', $valueExploded);
            // $valueExploded = str_replace(".", '', $valueExploded);
            // $valueExploded = str_replace("(", '', $valueExploded);
            // $valueExploded = str_replace(",", '', $valueExploded);
            // $valueExploded = str_replace("*", '', $valueExploded);
            $valueExploded = str_replace("'", '', $valueExploded);
            $valueExploded = preg_replace("/[^A-Za-z0-9 ]/", "", $valueExploded);
            array_push($explodedSongLyricsOrderedAndNonUnique, strtolower($valueExploded));
        }
    }
}

echo("<pre>");
echo " exploded song lyrics  \n";
print_r($explodedSongLyricsOrderedAndNonUnique);
echo("</pre>");

echo shell_exec("rm -R ./downloadedVideos/$channelTitle/$titleOfSong/orderedTrimmedFinalVideo");
mkdir("./downloadedVideos/$channelTitle/$titleOfSong/orderedTrimmedFinalVideo");

$arrayToCheckLyricCount = array();

foreach ($explodedSongLyricsOrderedAndNonUnique as $key => $valueLyric) {
    $arrayToCheckLyricCount[$valueLyric]++;

    $numberOfTimesThisLyricWasSaid = $arrayToCheckLyricCount[$valueLyric];
    $nameOfFile = $key.'_'.$valueLyric.'_'.$numberOfTimesThisLyricWasSaid;


    echo shell_exec("cp -R ./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo/$valueLyric/ ./downloadedVideos/$channelTitle/$titleOfSong/orderedTrimmedFinalVideo/$nameOfFile");
}
echo("<pre>");
echo "  arrayToCheckLyricCount \n";
print_r($arrayToCheckLyricCount);
echo("</pre>");
//---------------------------------------------------------------------------------------------
//add lyrics
$fromPath = "./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo";
$targetPath = "./downloadedVideos/$channelTitle/$titleOfSong/orderedTrimmedFinalVideo";
$arrayOfLyricsInFolder = scandir_only_wanted_files($targetPath);
sort($arrayOfLyricsInFolder, 1);

echo("<pre>");
echo "  arrayOfLyricsInFolder \n";
print_r($arrayOfLyricsInFolder);
echo("</pre>");

$filestructure  = dirToArray($fromPath);

foreach ($filestructure as $keyMainLyric => $valueArrayMainLyric) {
    foreach ($valueArrayMainLyric as $keySubword => $valueArraySubword) {
        if (is_array($valueArraySubword)) {
            foreach ($valueArraySubword as $keySimilarSubword => $valueArraySimilarSubword) {
                if (is_array($valueArraySimilarSubword)) {
                    foreach ($valueArraySimilarSubword as $key => $valueActualSimilarSubword) {
                        if (explode("-", $valueActualSimilarSubword)[0]== $keyMainLyric) {
                            foreach ($arrayOfLyricsInFolder as $keyLyricInFolder => $valueLyricInFolder) {
                                if (explode("_", $valueLyricInFolder)[1] == $keyMainLyric) {
                                    $name = "added#$valueActualSimilarSubword";
                                    echo "copying $fromPath/$keyMainLyric/$keySubword/$keySimilarSubword/$valueActualSimilarSubword to $targetPath/$valueLyricInFolder/$name \n";
                                    copy("$fromPath/$keyMainLyric/$keySubword/$keySimilarSubword/$valueActualSimilarSubword", "$targetPath/$valueLyricInFolder/$name");
                                }
                            }
                        }
                    }
                } else {
                    if (explode("-", $valueArraySimilarSubword)[0]==$keyMainLyric) {
                        foreach ($arrayOfLyricsInFolder as $keyLyricInFolder => $valueLyricInFolder) {
                            if (explode("_", $valueLyricInFolder)[1] == $keyMainLyric) {
                                $name = "added#$valueArraySimilarSubword";
                                echo "copying $fromPath/$keyMainLyric/$keySubword/$valueArraySimilarSubword to $targetPath/$valueLyricInFolder/$name \n";
                                copy("$fromPath/$keyMainLyric/$keySubword/$valueArraySimilarSubword", "$targetPath/$valueLyricInFolder/$name");
                            }
                        }
                    }
                }
            }
        }
    }
}
//---------------------------------------------------------------------------------------------
if ($shouldSeparateFinalAudioAndVideo) {

//video only
    echo shell_exec("rm -R ./downloadedVideos/$channelTitle/$titleOfSong/VideoOnlyOrderedTrimmedFinalVideo");
    mkdir("./downloadedVideos/$channelTitle/$titleOfSong/VideoOnlyOrderedTrimmedFinalVideo");

    $arrayToCheckLyricCount = array();

    foreach ($explodedSongLyricsOrderedAndNonUnique as $key => $valueLyric) {
        $arrayToCheckLyricCount[$valueLyric]++;

        $numberOfTimesThisLyricWasSaid = $arrayToCheckLyricCount[$valueLyric];
        $nameOfFile = $key.'_'.$valueLyric.'_'.$numberOfTimesThisLyricWasSaid;


        echo shell_exec("cp -R ./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly/$valueLyric/ ./downloadedVideos/$channelTitle/$titleOfSong/VideoOnlyOrderedTrimmedFinalVideo/$nameOfFile");
    }
    echo("<pre>");
    echo "  arrayToCheckLyricCount \n";
    print_r($arrayToCheckLyricCount);
    echo("</pre>");

    //---------------------------------------------------------------------------------------------
    //adding lyrics
    $fromPath = "./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly";
    $targetPath = "./downloadedVideos/$channelTitle/$titleOfSong/VideoOnlyOrderedTrimmedFinalVideo";
    $arrayOfLyricsInFolder = scandir_only_wanted_files($targetPath);
    sort($arrayOfLyricsInFolder, 1);

    echo("<pre>");
    echo "  arrayOfLyricsInFolder \n";
    print_r($arrayOfLyricsInFolder);
    echo("</pre>");

    $filestructure  = dirToArray($fromPath);

    foreach ($filestructure as $keyMainLyric => $valueArrayMainLyric) {
        foreach ($valueArrayMainLyric as $keySubword => $valueArraySubword) {
            if (is_array($valueArraySubword)) {
                foreach ($valueArraySubword as $keySimilarSubword => $valueArraySimilarSubword) {
                    if (is_array($valueArraySimilarSubword)) {
                        foreach ($valueArraySimilarSubword as $key => $valueActualSimilarSubword) {
                            if (explode("-", $valueActualSimilarSubword)[0]== $keyMainLyric) {
                                foreach ($arrayOfLyricsInFolder as $keyLyricInFolder => $valueLyricInFolder) {
                                    if (explode("_", $valueLyricInFolder)[1] == $keyMainLyric) {
                                        $name = "added#$valueActualSimilarSubword";
                                        echo "copying $fromPath/$keyMainLyric/$keySubword/$keySimilarSubword/$valueActualSimilarSubword to $targetPath/$valueLyricInFolder/$name \n";
                                        copy("$fromPath/$keyMainLyric/$keySubword/$keySimilarSubword/$valueActualSimilarSubword", "$targetPath/$valueLyricInFolder/$name");
                                    }
                                }
                            }
                        }
                    } else {
                        if (explode("-", $valueArraySimilarSubword)[0]==$keyMainLyric) {
                            foreach ($arrayOfLyricsInFolder as $keyLyricInFolder => $valueLyricInFolder) {
                                if (explode("_", $valueLyricInFolder)[1] == $keyMainLyric) {
                                    $name = "added#$valueArraySimilarSubword";
                                    echo "copying $fromPath/$keyMainLyric/$keySubword/$valueArraySimilarSubword to $targetPath/$valueLyricInFolder/$name \n";
                                    copy("$fromPath/$keyMainLyric/$keySubword/$valueArraySimilarSubword", "$targetPath/$valueLyricInFolder/$name");
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    //---------------------------------------------------------------------------------------------
    //audio only

    echo shell_exec("rm -R ./downloadedVideos/$channelTitle/$titleOfSong/AudioOnlyOrderedTrimmedFinalVideo");
    mkdir("./downloadedVideos/$channelTitle/$titleOfSong/AudioOnlyOrderedTrimmedFinalVideo");

    $arrayToCheckLyricCount = array();

    foreach ($explodedSongLyricsOrderedAndNonUnique as $key => $valueLyric) {
        $arrayToCheckLyricCount[$valueLyric]++;

        $numberOfTimesThisLyricWasSaid = $arrayToCheckLyricCount[$valueLyric];
        $nameOfFile = $key.'_'.$valueLyric.'_'.$numberOfTimesThisLyricWasSaid;


        echo shell_exec("cp -R ./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoAudioOnly/$valueLyric/ ./downloadedVideos/$channelTitle/$titleOfSong/AudioOnlyOrderedTrimmedFinalVideo/$nameOfFile");
    }
    echo("<pre>");
    echo "  arrayToCheckLyricCount \n";
    print_r($arrayToCheckLyricCount);
    echo("</pre>");

    $fromPath = "./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly";
    $targetPath = "./downloadedVideos/$channelTitle/$titleOfSong/VideoOnlyOrderedTrimmedFinalVideo";
    $arrayOfLyricsInFolder = scandir_only_wanted_files($targetPath);
    sort($arrayOfLyricsInFolder, 1);

    echo("<pre>");
    echo "  arrayOfLyricsInFolder \n";
    print_r($arrayOfLyricsInFolder);
    echo("</pre>");

    $filestructure  = dirToArray($fromPath);

    foreach ($filestructure as $keyMainLyric => $valueArrayMainLyric) {
        foreach ($valueArrayMainLyric as $keySubword => $valueArraySubword) {
            if (is_array($valueArraySubword)) {
                foreach ($valueArraySubword as $keySimilarSubword => $valueArraySimilarSubword) {
                    if (is_array($valueArraySimilarSubword)) {
                        foreach ($valueArraySimilarSubword as $key => $valueActualSimilarSubword) {
                            if (explode("-", $valueActualSimilarSubword)[0]== $keyMainLyric) {
                                foreach ($arrayOfLyricsInFolder as $keyLyricInFolder => $valueLyricInFolder) {
                                    if (explode("_", $valueLyricInFolder)[1] == $keyMainLyric) {
                                        $name = "added#$valueActualSimilarSubword";
                                        echo "copying $fromPath/$keyMainLyric/$keySubword/$keySimilarSubword/$valueActualSimilarSubword to $targetPath/$valueLyricInFolder/$name \n";
                                        copy("$fromPath/$keyMainLyric/$keySubword/$keySimilarSubword/$valueActualSimilarSubword", "$targetPath/$valueLyricInFolder/$name");
                                    }
                                }
                            }
                        }
                    } else {
                        if (explode("-", $valueArraySimilarSubword)[0]==$keyMainLyric) {
                            foreach ($arrayOfLyricsInFolder as $keyLyricInFolder => $valueLyricInFolder) {
                                if (explode("_", $valueLyricInFolder)[1] == $keyMainLyric) {
                                    $name = "added#$valueArraySimilarSubword";
                                    echo "copying $fromPath/$keyMainLyric/$keySubword/$valueArraySimilarSubword to $targetPath/$valueLyricInFolder/$name \n";
                                    copy("$fromPath/$keyMainLyric/$keySubword/$valueArraySimilarSubword", "$targetPath/$valueLyricInFolder/$name");
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
//---------------------------------------------------------------------------------------------
if ($shouldAutotune) {
    require "autotune.php";
}
if ($shouldAutoGenerateFinalVideo) {
    require "botVideoCreation.php";
}
//---------------------------------------------------------------------------------------------
echo("</pre>");
echo 'end time: '.  date("Y-m-d H:i:s");

echo 'end of index.php';
