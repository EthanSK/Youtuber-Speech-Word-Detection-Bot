<?php
echo("<pre>");


$ffmpeg = './ffmpeg';
$youtubedl = './youtube-dl';
exec('unset DYLD_LIBRARY_PATH ;');
putenv('DYLD_LIBRARY_PATH');
putenv('DYLD_LIBRARY_PATH=/usr/bin');
echo 'start of watson';
$limiterLimit = 1000000; //i.e. no limit
$limiterStartLimit = 0;
if (!isset($amountToAddToEndAndSubtractFromStart)) {
    $amountToAddToEndAndSubtractFromStart = 0.3;
}

//---------------------------------------------------------------------------------------------
//get subfilesystem
require_once('functions.php');
$audioClipsOfLyrics =  scandir_only_wanted_files("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics");
$arrayOfLyricsWithAllTheirSubLyricsETC = array();
foreach ($audioClipsOfLyrics as $keyLyric => $valueLyric) {
    $insideEachLyricDir = scandir_only_wanted_files("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$valueLyric");
    $arrayOfLyricsWithAllTheirSubLyricsETC[$valueLyric] =array();
    foreach ($insideEachLyricDir as $keyLyricFiles => $valueLyricFiles) {
        array_push($arrayOfLyricsWithAllTheirSubLyricsETC[$valueLyric], $valueLyricFiles);

        if (is_dir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$valueLyric/$valueLyricFiles")) {
            $arrayOfLyricsWithAllTheirSubLyricsETC[$valueLyric][$valueLyricFiles] = array();
            unset($arrayOfLyricsWithAllTheirSubLyricsETC[$valueLyric][$keyLyricFiles]);
            $insideEachSubLyricDir = scandir_only_wanted_files("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$valueLyric/$valueLyricFiles");

            foreach ($insideEachSubLyricDir as $keySubLyricFiles => $valueSubLyricFiles) {
                array_push($arrayOfLyricsWithAllTheirSubLyricsETC[$valueLyric][$valueLyricFiles], $valueSubLyricFiles);

                if (is_dir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$valueLyric/$valueLyricFiles/$valueSubLyricFiles")) {
                    $arrayOfLyricsWithAllTheirSubLyricsETC[$valueLyric][$valueLyricFiles][$valueSubLyricFiles] = array();
                    unset($arrayOfLyricsWithAllTheirSubLyricsETC[$valueLyric][$valueLyricFiles][$keySubLyricFiles]);

                    $insideEachSubLyricFiles = scandir_only_wanted_files("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$valueLyric/$valueLyricFiles/$valueSubLyricFiles");
                    foreach ($insideEachSubLyricFiles as $keySimilarSubLyricFiles => $valueSimilarSubLyricFiles) {
                        array_push($arrayOfLyricsWithAllTheirSubLyricsETC[$valueLyric][$valueLyricFiles][$valueSubLyricFiles], $valueSimilarSubLyricFiles);
                        if (is_dir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$valueLyric/$valueLyricFiles/$valueSubLyricFiles/$valueSimilarSubLyricFiles")) {
                            $arrayOfLyricsWithAllTheirSubLyricsETC[$valueLyric][$valueLyricFiles][$valueSubLyricFiles][$valueSimilarSubLyricFiles] = array();
                            unset($arrayOfLyricsWithAllTheirSubLyricsETC[$valueLyric][$valueLyricFiles][$valueSubLyricFiles][$keySimilarSubLyricFiles]);
                        }
                    }
                }
            }
        }
    }
}

echo("<pre>");
echo "  araryOfLyricsWithAllTheirSubLyricsETC \n";
print_r($arrayOfLyricsWithAllTheirSubLyricsETC);
echo("</pre>");
//---------------------------------------------------------------------------------------------
//send dat shit to watson and other functions



//TURN THESE BACK ON - um looks like i never did and now i dunno if i should or nah
echo shell_exec("rm -R ./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo");

// $arrayOfWordThatAreSpelledWrong = array(
//   "i" => "I",
//   "ill" => "I'll",
//   "id"=>"I'd",
//   "im"=>"I'm",
//   "youd"=>"you'd",
//   "youve"=>"you've",
//   "hes"=>"he's",
// "shes"=>"she's",
// "theyre"=>"they're",
// "thats"=>"that's",
// "whos"=>"who's",
// );

function check_original_word_for_contraction($currentWord)
{
    global $songLyrics;
    if (!isset($songLyrics)) {
        $songLyrics = file('songLyrics.txt', FILE_IGNORE_NEW_LINES);
    }
    $explodedSongLyricsWithRealContractions = array();
    $explodedSongLyrics = array();
    foreach ($songLyrics as $key => $value) {
        foreach (explode(" ", $value) as $keyExploded => $valueExploded) {
            if (!empty($valueExploded)) {
                $foundApostrAtStartOrEnd = false;

                if (stripos($valueExploded, "'") === 0) {
                    $valueExploded = str_replace("'", '', $valueExploded);
                    $foundApostrAtStartOrEnd = true;
                }
                if (stripos($valueExploded, "'") === strlen($valueExploded) -1) {
                    $valueExploded = str_replace("'", '', $valueExploded);
                    $foundApostrAtStartOrEnd = true;
                }

                //if the second letter is not an apostr, make in lower case
                if (stripos($valueExploded, "'") !== 1 && $valueExploded != 'I') {
                    $valueExploded =strtolower($valueExploded);
                }

                $valueExploded = preg_replace("/[^A-Za-z0-9' ]/", "", $valueExploded);
                array_push($explodedSongLyricsWithRealContractions, $valueExploded);
                //now push them without contractions to compare to current word
                $valueExploded = preg_replace("/[^A-Za-z0-9 ]/", "", $valueExploded);
                array_push($explodedSongLyrics, strtolower($valueExploded));
            }
        }
    }

    foreach ($explodedSongLyrics as $key => $valueLyric) {
        if ($currentWord ==$valueLyric && $currentWord != $explodedSongLyricsWithRealContractions[$key]) {
            echo("<pre>");
            echo "  found contraction \n";
            print_r($currentWord);
            echo("</pre>");
            echo 'should be '.$explodedSongLyricsWithRealContractions[$key].' ';
            return $explodedSongLyricsWithRealContractions[$key];
        } elseif ($currentWord ==$valueLyric && $currentWord == $explodedSongLyricsWithRealContractions[$key]) {
            echo 'stays at '. $explodedSongLyricsWithRealContractions[$key].' ';
            return $explodedSongLyricsWithRealContractions[$key];
        }
    }
}
//---------------------------------------------------------------------------------------------
//legit and alternative

//to sort, sort the n different keyword occurances based on the best inner keyword value. Best could mean highest confidenc, or longest duration or whatever i deem best.
//lets just stick to sorting by confidence for both cases


// if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalLyrics"))
// 	{
// 	mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalLyrics");
// 	}
  if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo")) {
      mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo");
  }


$limiter = 0;

foreach ($arrayOfLyricsWithAllTheirSubLyricsETC as $keyMainLyric => $valueArrayOuter1) {
    $limiter++;
    if ($limiter >$limiterLimit) {
        continue ;
    }
    if ($limiter < $limiterStartLimit) {
        continue ;
    }

    if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo/$keyMainLyric")) {
        mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo/$keyMainLyric");
    }
    $arrayOfWinnersFromInsideEachFile = array();

    foreach ($valueArrayOuter1 as $valueLegitOrAlternatives) {
        // this loop ^ is for ALL the files for ONE lyric.
        if (!is_array($valueLegitOrAlternatives)) {
            $path = "./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$keyMainLyric/$valueLegitOrAlternatives";

            $usingAlternativeLyric = false;
            if (stripos($valueLegitOrAlternatives, 'alternative')!== false) {
                $keyMainLyricAlternative = explode("~", substr($valueLegitOrAlternatives, 0, -5))[1];
                echo 'alternative main lyric '. $keyMainLyricAlternative;
                echo check_original_word_for_contraction($keyMainLyricAlternative);
                $responseArray = use_watson($path, check_original_word_for_contraction($keyMainLyricAlternative));
                $usingAlternativeLyric = true;
            } else {
                echo check_original_word_for_contraction($keyMainLyric);
                $responseArray = use_watson($path, check_original_word_for_contraction($keyMainLyric));
            }
            //if we use alternatives, then we need to change the lyric key in the response array because below we refer to values inside that key by their actual key main lyric name not their 'alternative' name
            foreach ($responseArray['results'] as $keyResults => $valueResults) {
                if ($usingAlternativeLyric && !empty($responseArray['results'][$keyResults]['keywords_result'])) {
                    echo 'alternative main lyric '.$keyMainLyricAlternative;
                    $responseArray['results'][$keyResults]['keywords_result'][$keyMainLyric] = $responseArray['results'][$keyResults]['keywords_result'][$keyMainLyricAlternative];
                    unset($responseArray['results'][$keyResults]['keywords_result'][$keyMainLyricAlternative]);
                }
            }

            echo 'this is the key main lyric: '.check_original_word_for_contraction($keyMainLyric);
            foreach ($responseArray['results'] as $keyResults => $valueResults) {
                // doing this because when trying to sort we cant have same key names
                unset($responseArray['results'][$keyResults]['alternatives'][0]["confidence"]);
                if (!empty($responseArray['results'][$keyResults]['keywords_result'])) {
                    usort(
            $responseArray['results'][$keyResults]['keywords_result'][check_original_word_for_contraction($keyMainLyric)],
        function ($a, $b) {
            return $a['confidence']*1000 - $b['confidence']*1000;
        }
        );
                    $responseArray['results'][$keyResults]['keywords_result'][check_original_word_for_contraction($keyMainLyric)] =  array_reverse($responseArray['results'][$keyResults]['keywords_result'][check_original_word_for_contraction($keyMainLyric)]);
                    //
                    // echo ("<pre>");
                    // echo " keywords sorted \n";
                    // print_r($responseArray['results'][$keyResults]['keywords_result'][check_original_word_for_contraction($keyMainLyric)]);
                    // echo ("</pre>");

                    //after sorting it, unset if there are multiple keywords for one phrase so that the usort based on confidence isnt confused
                    foreach ($responseArray['results'][$keyResults]['keywords_result'][check_original_word_for_contraction($keyMainLyric)] as $keyKeywords =>  $valueKeywords) {
                        if ($keyKeywords > 0) {
                            unset($responseArray['results'][$keyResults]['keywords_result'][check_original_word_for_contraction($keyMainLyric)][$keyKeywords]);
                        }
                    }
                }

                if (empty($responseArray['results'][$keyResults]['keywords_result'])) {
                    unset($responseArray['results'][$keyResults]);
                } else {
                    //add duration element to array
                    $responseArray['results'][$keyResults]['keywords_result'][check_original_word_for_contraction($keyMainLyric)][0]["duration"] =
          $responseArray['results'][$keyResults]['keywords_result'][check_original_word_for_contraction($keyMainLyric)][0]["end_time"]-
          $responseArray['results'][$keyResults]['keywords_result'][check_original_word_for_contraction($keyMainLyric)][0]["start_time"];
                    $responseArray['results'][$keyResults]['file_name'] = $valueLegitOrAlternatives;
                }
            }
            //inside the file we want to sort always based on confidence
            usort(
            $responseArray['results'],
        function ($a, $b) {
            global $keyMainLyric;

            return $a['keywords_result'][check_original_word_for_contraction($keyMainLyric)][0]['confidence']*1000 - $b['keywords_result'][check_original_word_for_contraction($keyMainLyric)][0]['confidence']*1000;
        }
        );
            $responseArray['results'] =  array_reverse($responseArray['results']);
            echo("<pre>");
            echo " response array sorted for lyric $keyMainLyric\n";
            print_r($responseArray['results']);
            echo("</pre>");
            //place winner of the inside keyword confidence in a new array
            if (!empty($responseArray['results'][0])) {
                array_push($arrayOfWinnersFromInsideEachFile, $responseArray['results'][0]);
            }
        }
    }

    usort(
          $arrayOfWinnersFromInsideEachFile,
        function ($a, $b) {
            global $keyMainLyric;
            echo 'this is keyMainlyric '.check_original_word_for_contraction($keyMainLyric);

            return $a['keywords_result'][check_original_word_for_contraction($keyMainLyric)][0]['confidence']*1000 - $b['keywords_result'][check_original_word_for_contraction($keyMainLyric)][0]['confidence']*1000;
        }
      );
    $arrayOfWinnersFromInsideEachFile = array_reverse($arrayOfWinnersFromInsideEachFile);
    echo("<pre>");
    echo "  these are the winners of a whole lyric in order \n";
    print_r($arrayOfWinnersFromInsideEachFile);
    echo("</pre>");

    foreach ($arrayOfWinnersFromInsideEachFile as $keyInOrderOfWinners => $valueKeywordArrayInfo) {
        //$fileName = substr($valueKeywordArrayInfo["file_name"], 0, -4).'mp4';
        $fileName =pathinfo($valueKeywordArrayInfo["file_name"])['filename'];
        $path = "./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$keyMainLyric/$fileName.mp4";
        //the reason it should actually add no time to start and end time is because if we want a longer version of the clip well we already have it in the other file. videoAndAudioClipsOfLyrics
        //ok so the reason we cant have it at 0 is coz short words are so short they basically have no video play time
        $startTime = max($valueKeywordArrayInfo['keywords_result'][check_original_word_for_contraction($keyMainLyric)][0]['start_time']-$amountToAddToEndAndSubtractFromStart, 0);
        $endTime = $valueKeywordArrayInfo['keywords_result'][check_original_word_for_contraction($keyMainLyric)][0]['end_time']+$amountToAddToEndAndSubtractFromStart;
        echo 'start '.$startTime. ' end '. $endTime. ' duration '. $startTime-$endTime;

        $idOfThisVideo = explode('*', $valueKeywordArrayInfo['file_name'])[1];
        //-------------------------------------------
        $startTimeWithoutTimeAddedOrSubtracted = $valueKeywordArrayInfo['keywords_result'][check_original_word_for_contraction($keyMainLyric)][0]['start_time'];

        $endTimeWithoutTimeAddedOrSubtracted = $valueKeywordArrayInfo['keywords_result'][check_original_word_for_contraction($keyMainLyric)][0]['end_time'];
        //start*end*from
        //we don't need that extra info for the below video and audio only things. dont wanna mess things up by accident either.
        $fileInfo = "$startTimeWithoutTimeAddedOrSubtracted*$endTimeWithoutTimeAddedOrSubtracted*$fileName";
        //-------------------------------------------
        echo shell_exec("$ffmpeg  -i $path -y -ss $startTime -to $endTime  ./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo/$keyMainLyric/$keyMainLyric-$keyInOrderOfWinners*$fileInfo.mp4 2>&1");


        if ($shouldSeparateFinalAudioAndVideo) {
            if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly")) {
                mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly");
            }


            if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly/$keyMainLyric")) {
                mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly/$keyMainLyric");
            }

            if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoAudioOnly")) {
                mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoAudioOnly");
            }


            if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoAudioOnly/$keyMainLyric")) {
                mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoAudioOnly/$keyMainLyric");
            }

            echo shell_exec("$ffmpeg  -i $path -y -ss $startTime -to $endTime  -an ./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly/$keyMainLyric/$keyMainLyric-$keyInOrderOfWinners*$fileInfo.mp4 2>&1");
            echo shell_exec("$ffmpeg  -i $path -y -ss $startTime -to $endTime -vn ./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoAudioOnly/$keyMainLyric/$keyMainLyric-$keyInOrderOfWinners*$fileInfo.aiff 2>&1");
        }
    }
    //i need to youtube dl this as in the actual video not dl audio

    echo 'end of this lyric';
}
//---------------------------------------------------------------------------------------------
//subWords

$limiter = 0;

foreach ($arrayOfLyricsWithAllTheirSubLyricsETC as $keyMainLyric => $valueArrayOuter1) {
    $limiter++;
    if ($limiter >$limiterLimit) {
        continue ;
    }
    if ($limiter < $limiterStartLimit) {
        continue ;
    }
    if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo/$keyMainLyric")) {
        mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo/$keyMainLyric");
    }
    //here valueLegitOrAlternatives will represent the folder with the subwords not the actual webm legit or alternative file
    foreach ($valueArrayOuter1 as $keySubLyricActualWord => $valueLegitOrAlternatives) {
        $arrayOfWinnersFromInsideEachFile = array();

        // this loop ^ is for ALL the files for ONE lyric.
        if (is_array($valueLegitOrAlternatives)) {
            if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo/$keyMainLyric/$keySubLyricActualWord")) {
                mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo/$keyMainLyric/$keySubLyricActualWord");
            }

            foreach ($valueLegitOrAlternatives as $keySubword => $valueSubword) {
                if (!is_array($valueSubword)) {
                    $path = "./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$keyMainLyric/$keySubLyricActualWord/$valueSubword";

                    $responseArray = use_watson($path, $keySubLyricActualWord);

                    echo 'this is the subword  lyric: '.$keySubLyricActualWord;
                    foreach ($responseArray['results'] as $keyResults => $valueResults) {
                        // doing this because when trying to sort we cant have same key names
                        unset($responseArray['results'][$keyResults]['alternatives'][0]["confidence"]);
                        if (!empty($responseArray['results'][$keyResults]['keywords_result'])) {
                            usort(
                    $responseArray['results'][$keyResults]['keywords_result'][$keySubLyricActualWord],
                function ($a, $b) {
                    return $a['confidence']*1000 - $b['confidence']*1000;
                }
                );
                            $responseArray['results'][$keyResults]['keywords_result'][$keySubLyricActualWord] =  array_reverse($responseArray['results'][$keyResults]['keywords_result'][$keySubLyricActualWord]);
                            //
                            echo("<pre>");
                            echo " keywords sorted \n";
                            print_r($responseArray['results'][$keyResults]['keywords_result'][$keySubLyricActualWord]);
                            echo("</pre>");

                            //after sorting it, unset if there are multiple keywords for one phrase so that the usort based on confidence isnt confused
                            foreach ($responseArray['results'][$keyResults]['keywords_result'][$keySubLyricActualWord] as $keyKeywords =>  $valueKeywords) {
                                if ($keyKeywords > 0) {
                                    unset($responseArray['results'][$keyResults]['keywords_result'][$keySubLyricActualWord][$keyKeywords]);
                                }
                            }
                        }

                        if (empty($responseArray['results'][$keyResults]['keywords_result'])) {
                            unset($responseArray['results'][$keyResults]);
                        } else {
                            //add duration element to array
                            $responseArray['results'][$keyResults]['keywords_result'][$keySubLyricActualWord][0]["duration"] =
                  $responseArray['results'][$keyResults]['keywords_result'][$keySubLyricActualWord][0]["end_time"]-
                  $responseArray['results'][$keyResults]['keywords_result'][$keySubLyricActualWord][0]["start_time"];
                            $responseArray['results'][$keyResults]['file_name'] = $valueLegitOrAlternatives[$keySubword];
                        }
                    }
                    //inside the file we want to sort always based on confidence
                    usort(
                    $responseArray['results'],
                function ($a, $b) {
                    global $keySubLyricActualWord;

                    return $a['keywords_result'][$keySubLyricActualWord][0]['confidence']*1000 - $b['keywords_result'][$keySubLyricActualWord][0]['confidence']*1000;
                }
                );
                    $responseArray['results'] =  array_reverse($responseArray['results']);
                    echo("<pre>");
                    echo " response array sorted for lyric $keySubLyricActualWord\n";
                    print_r($responseArray['results']);
                    echo("</pre>");
                    //place winner of the inside keyword confidence in a new array
                    if (!empty($responseArray['results'][0])) {
                        array_push($arrayOfWinnersFromInsideEachFile, $responseArray['results'][0]);
                    }
                }
            }
        }
        usort(
          $arrayOfWinnersFromInsideEachFile,
        function ($a, $b) {
            global $keySubLyricActualWord;
            echo 'this is subkeyMainlyric '.$keySubLyricActualWord;

            return $a['keywords_result'][$keySubLyricActualWord][0]['confidence']*1000 - $b['keywords_result'][$keySubLyricActualWord][0]['confidence']*1000;
        }
      );
        $arrayOfWinnersFromInsideEachFile = array_reverse($arrayOfWinnersFromInsideEachFile);
        echo("<pre>");
        echo "  these are the winners of a whole lyric in order \n";
        print_r($arrayOfWinnersFromInsideEachFile);
        echo("</pre>");

        foreach ($arrayOfWinnersFromInsideEachFile as $keyInOrderOfWinners => $valueKeywordArrayInfo) {
            //$fileName = substr($valueKeywordArrayInfo["file_name"], 0, -4).'mp4';
            $fileName =pathinfo($valueKeywordArrayInfo["file_name"])['filename'];

            $path = "./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$keyMainLyric/$keySubLyricActualWord/$fileName.mp4";
            //the reason it should actually add no time to start and end time is because if we want a longer version of the clip well we already have it in the other file. videoAndAudioClipsOfLyrics
            $startTime = max($valueKeywordArrayInfo['keywords_result'][$keySubLyricActualWord][0]['start_time']-$amountToAddToEndAndSubtractFromStart, 0);
            $endTime = $valueKeywordArrayInfo['keywords_result'][$keySubLyricActualWord][0]['end_time']+$amountToAddToEndAndSubtractFromStart;
            echo 'start '.$startTime. ' end '. $endTime. ' duration '. $startTime-$endTime;

            $idOfThisVideo = explode('*', $valueKeywordArrayInfo['file_name'])[1];
            //-------------------------------------------
            $startTimeWithoutTimeAddedOrSubtracted = $valueKeywordArrayInfo['keywords_result'][$keySubLyricActualWord][0]['start_time'];

            $endTimeWithoutTimeAddedOrSubtracted = $valueKeywordArrayInfo['keywords_result'][$keySubLyricActualWord][0]['end_time'];
            //start*end*from
            //we don't need that extra info for the below video and audio only things. dont wanna mess things up by accident either.
            //turns out by not changing it we are messing thigns up. autotune requires that the video and audio files are the same name as the audio only...
            $fileInfo = "$startTimeWithoutTimeAddedOrSubtracted*$endTimeWithoutTimeAddedOrSubtracted*$fileName";
            //-------------------------------------------

            echo shell_exec("$ffmpeg  -i $path -y -ss $startTime -to $endTime  ./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo/$keyMainLyric/$keySubLyricActualWord/$keySubLyricActualWord-$keyInOrderOfWinners*$fileInfo.mp4 2>&1");

            if ($shouldSeparateFinalAudioAndVideo) {
                if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly/$keyMainLyric")) {
                    mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly/$keyMainLyric");
                }



                if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoAudioOnly/$keyMainLyric")) {
                    mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoAudioOnly/$keyMainLyric");
                }
                //00000090900909090090909090909090909090990090909

                if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly/$keyMainLyric/$keySubLyricActualWord")) {
                    mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly/$keyMainLyric/$keySubLyricActualWord");
                }
                if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoAudioOnly/$keyMainLyric/$keySubLyricActualWord")) {
                    mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoAudioOnly/$keyMainLyric/$keySubLyricActualWord");
                }

                echo shell_exec("$ffmpeg  -i $path -y -ss $startTime -to $endTime  -an ./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly/$keyMainLyric/$keySubLyricActualWord/$keySubLyricActualWord-$keyInOrderOfWinners*$fileInfo.mp4 2>&1");
                echo shell_exec("$ffmpeg  -i $path -y -ss $startTime -to $endTime -vn ./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoAudioOnly/$keyMainLyric/$keySubLyricActualWord/$keySubLyricActualWord-$keyInOrderOfWinners*$fileInfo.aiff 2>&1");
            }
        }
    }
}
//---------------------------------------------------------------------------------------------
//similarSubWords

$limiter = 0;

foreach ($arrayOfLyricsWithAllTheirSubLyricsETC as $keyMainLyric => $valueArrayOuter1) {
    $limiter++;
    if ($limiter >$limiterLimit) {
        continue ;
    }
    if ($limiter < $limiterStartLimit) {
        continue ;
    }
    if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo/$keyMainLyric")) {
        mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo/$keyMainLyric");
    }
    //here valueLegitOrAlternatives will represent the folder with the subwords not the actual webm legit or alternative file
    foreach ($valueArrayOuter1 as $keySubLyricActualWord => $valueLegitOrAlternatives) {

        // this loop ^ is for ALL the files for ONE lyric.
        if (is_array($valueLegitOrAlternatives)) {
            if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo/$keyMainLyric/$keySubLyricActualWord")) {
                mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo/$keyMainLyric/$keySubLyricActualWord");
            }
            //again, here $valueSubword will represent the folder with the subwords not the actual webm legit or alternative file so it could be a folder or a file

            foreach ($valueLegitOrAlternatives as $keySimilarSubLyricActualWord => $valueSubword) {
                $arrayOfWinnersFromInsideEachFile = array();

                if (is_array($valueSubword)) {
                    if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo/$keyMainLyric/$keySubLyricActualWord/$keySimilarSubLyricActualWord")) {
                        mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo/$keyMainLyric/$keySubLyricActualWord/$keySimilarSubLyricActualWord");
                    }
                    foreach ($valueSubword as $keySimilarSubword => $valueSimilarSubWord) {
                        //technically we dont need this if because there will only be files this deep in, but meh lets keep it for continuity
                        if (!is_array($valueSimilarSubWord)) {
                            $path = "./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$keyMainLyric/$keySubLyricActualWord/$keySimilarSubLyricActualWord/$valueSimilarSubWord";

                            $responseArray = use_watson($path, $keySimilarSubLyricActualWord);

                            echo 'this is the similar subword  lyric: '.$keySimilarSubLyricActualWord;
                            foreach ($responseArray['results'] as $keyResults => $valueResults) {
                                // doing this because when trying to sort we cant have same key names
                                unset($responseArray['results'][$keyResults]['alternatives'][0]["confidence"]);
                                if (!empty($responseArray['results'][$keyResults]['keywords_result'])) {
                                    usort(
                        $responseArray['results'][$keyResults]['keywords_result'][$keySimilarSubLyricActualWord],
                    function ($a, $b) {
                        return $a['confidence']*1000 - $b['confidence']*1000;
                    }
                    );
                                    $responseArray['results'][$keyResults]['keywords_result'][$keySimilarSubLyricActualWord] =  array_reverse($responseArray['results'][$keyResults]['keywords_result'][$keySimilarSubLyricActualWord]);
                                    //
                                    echo("<pre>");
                                    echo " keywords sorted \n";
                                    print_r($responseArray['results'][$keyResults]['keywords_result'][$keySimilarSubLyricActualWord]);
                                    echo("</pre>");

                                    //after sorting it, unset if there are multiple keywords for one phrase so that the usort based on confidence isnt confused
                                    foreach ($responseArray['results'][$keyResults]['keywords_result'][$keySimilarSubLyricActualWord] as $keyKeywords =>  $valueKeywords) {
                                        if ($keyKeywords > 0) {
                                            unset($responseArray['results'][$keyResults]['keywords_result'][$keySimilarSubLyricActualWord][$keyKeywords]);
                                        }
                                    }
                                }

                                if (empty($responseArray['results'][$keyResults]['keywords_result'])) {
                                    unset($responseArray['results'][$keyResults]);
                                } else {
                                    //add duration element to array
                                    $responseArray['results'][$keyResults]['keywords_result'][$keySimilarSubLyricActualWord][0]["duration"] =
                      $responseArray['results'][$keyResults]['keywords_result'][$keySimilarSubLyricActualWord][0]["end_time"]-
                      $responseArray['results'][$keyResults]['keywords_result'][$keySimilarSubLyricActualWord][0]["start_time"];
                                    $responseArray['results'][$keyResults]['file_name'] = $valueLegitOrAlternatives[$keySimilarSubLyricActualWord][$keySimilarSubword];
                                }
                            }
                            //inside the file we want to sort always based on confidence
                            usort(
                        $responseArray['results'],
                    function ($a, $b) {
                        global $keySimilarSubLyricActualWord;

                        return $a['keywords_result'][$keySimilarSubLyricActualWord][0]['confidence']*1000 - $b['keywords_result'][$keySimilarSubLyricActualWord][0]['confidence']*1000;
                    }
                    );
                            $responseArray['results'] =  array_reverse($responseArray['results']);
                            echo("<pre>");
                            echo " response array sorted for lyric $keySimilarSubLyricActualWord\n";
                            print_r($responseArray['results']);
                            echo("</pre>");
                            //place winner of the inside keyword confidence in a new array
                            if (!empty($responseArray['results'][0])) {
                                array_push($arrayOfWinnersFromInsideEachFile, $responseArray['results'][0]);
                            }
                        }
                    }
                }


                usort(
                    $arrayOfWinnersFromInsideEachFile,
                  function ($a, $b) {
                      global $keySimilarSubLyricActualWord;
                      echo 'this is keySimilarSubLyricActualWord '.$keySimilarSubLyricActualWord;

                      return $a['keywords_result'][$keySimilarSubLyricActualWord][0]['confidence']*1000 - $b['keywords_result'][$keySimilarSubLyricActualWord][0]['confidence']*1000;
                  }
                );
                $arrayOfWinnersFromInsideEachFile = array_reverse($arrayOfWinnersFromInsideEachFile);
                echo("<pre>");
                echo "  these are the winners of a whole lyric in order \n";
                print_r($arrayOfWinnersFromInsideEachFile);
                echo("</pre>");

                foreach ($arrayOfWinnersFromInsideEachFile as $keyInOrderOfWinners => $valueKeywordArrayInfo) {
                    //$fileName = substr($valueKeywordArrayInfo["file_name"], 0, -4).'mp4';
                    $fileName =pathinfo($valueKeywordArrayInfo["file_name"])['filename'];

                    $path = "./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$keyMainLyric/$keySubLyricActualWord/$keySimilarSubLyricActualWord/$fileName.mp4";
                    //the reason it should actually add no time to start and end time is because if we want a longer version of the clip well we already have it in the other file. videoAndAudioClipsOfLyrics
                    $startTime = max($valueKeywordArrayInfo['keywords_result'][$keySimilarSubLyricActualWord][0]['start_time']-$amountToAddToEndAndSubtractFromStart, 0);
                    $endTime = $valueKeywordArrayInfo['keywords_result'][$keySimilarSubLyricActualWord][0]['end_time']+$amountToAddToEndAndSubtractFromStart;
                    echo 'start '.$startTime. ' end '. $endTime. ' duration '. $startTime-$endTime;

                    $idOfThisVideo = explode('*', $valueKeywordArrayInfo['file_name'])[1];
                    //-------------------------------------------
                    $startTimeWithoutTimeAddedOrSubtracted = $valueKeywordArrayInfo['keywords_result'][$keySimilarSubLyricActualWord][0]['start_time'];

                    $endTimeWithoutTimeAddedOrSubtracted = $valueKeywordArrayInfo['keywords_result'][$keySimilarSubLyricActualWord][0]['end_time'];
                    //start*end*from
                    //we don't need that extra info for the below video and audio only things. dont wanna mess things up by accident either.
                    $fileInfo = "$startTimeWithoutTimeAddedOrSubtracted*$endTimeWithoutTimeAddedOrSubtracted*$fileName";
                    //-------------------------------------------
                    echo shell_exec("$ffmpeg  -i $path -y -ss $startTime -to $endTime  ./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideo/$keyMainLyric/$keySubLyricActualWord/$keySimilarSubLyricActualWord/$keySimilarSubLyricActualWord-$keyInOrderOfWinners*$fileInfo.mp4 2>&1");


                    if ($shouldSeparateFinalAudioAndVideo) {
                        if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly/$keyMainLyric")) {
                            mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly/$keyMainLyric");
                        }



                        if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoAudioOnly/$keyMainLyric")) {
                            mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoAudioOnly/$keyMainLyric");
                        }
                        //00000090900909090090909090909090909090990090909

                        if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly/$keyMainLyric/$keySubLyricActualWord")) {
                            mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly/$keyMainLyric/$keySubLyricActualWord");


                            if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoAudioOnly/$keyMainLyric/$keySubLyricActualWord")) {
                                mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoAudioOnly/$keyMainLyric/$keySubLyricActualWord");
                            }
                            //00000090900909090090909090909090909090990090909

                            if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly/$keyMainLyric/$keySubLyricActualWord/$keySimilarSubLyricActualWord")) {
                                mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly/$keyMainLyric/$keySubLyricActualWord/$keySimilarSubLyricActualWord");
                            }

                            if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoAudioOnly/$keyMainLyric/$keySubLyricActualWord/$keySimilarSubLyricActualWord")) {
                                mkdir("./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoAudioOnly/$keyMainLyric/$keySubLyricActualWord/$keySimilarSubLyricActualWord");
                            }

                            echo shell_exec("$ffmpeg  -i $path -y -ss $startTime -to $endTime  -an ./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoVideoOnly/$keyMainLyric/$keySubLyricActualWord/$keySimilarSubLyricActualWord/$keySimilarSubLyricActualWord-$keyInOrderOfWinners*$fileInfo.mp4 2>&1");
                            echo shell_exec("$ffmpeg  -i $path -y -ss $startTime -to $endTime -vn ./downloadedVideos/$channelTitle/$titleOfSong/trimmedFinalVideoAudioOnly/$keyMainLyric/$keySubLyricActualWord/$keySimilarSubLyricActualWord/$keySimilarSubLyricActualWord-$keyInOrderOfWinners*$fileInfo.aiff 2>&1");
                        }
                    }
                }
            }
        }
    }
}


//---------------------------------------------------------------------------------------------
echo("<pre>");

echo 'end of watson';

echo("</pre>");
