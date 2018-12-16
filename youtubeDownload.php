<?php
require __DIR__ . '/vendor/autoload.php';
echo("<pre>");
echo 'start of youtubeDownload ';
if (!is_dir("./downloadedVideos/$channelTitle")) {
    mkdir("./downloadedVideos/$channelTitle");
}
if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong")) {
    mkdir("./downloadedVideos/$channelTitle/$titleOfSong");
}
if (!isset($amountToAddToEndAndSubtractFromStartLongClips)) {
    $amountToAddToEndAndSubtractFromStartLongClips = 3;
}

//---------------------------------------------------------------------------------------------
//download all videos that are gonna be used
//keep all the downloaded videos in a main dir outside the individual song so we can use it on future videos
$arrayOfDownloadedAudioTracks = array();
foreach ($arrayOfMatches as $key => $value) {
    //we need to download all the videos audio tracks, but only once.
    echo("<pre>");
    echo " value video id  \n";
    print_r($value['id']);
    echo("</pre>");
    if (!file_exists("./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id].webm")) {
        echo shell_exec("$youtubedl https://www.youtube.com/watch?v=$value[id] --ffmpeg-location ./ffmpeg  --no-overwrites -f bestaudio[ext=webm]/bestaudio -o ./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id].webm  2>&1");

        $fileInfo = new SplFileInfo("./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id]");
        if ($fileInfo->getExtension() != 'webm') {
            echo 'audio not webm so converting';

            echo shell_exec("$ffmpeg  -i ./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id] -y -qscale 0 ./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id].webm 2>&1");
        }
    }
    if (!file_exists("./downloadedVideos/$channelTitle/videoAndAudio/$value[id].mp4")) {
        echo shell_exec("$youtubedl https://www.youtube.com/watch?v=$value[id] --ffmpeg-location ./ffmpeg --no-overwrites -f bestvideo[ext=mp4]+bestaudio[ext=m4a]/mp4 -o ./downloadedVideos/$channelTitle/videoAndAudio/$value[id].mp4 2>&1");

        $fileInfo = new SplFileInfo("./downloadedVideos/$channelTitle/videoAndAudio/$value[id]");
        if ($fileInfo->getExtension() != 'mp4') {
            echo 'video not mp4 so converting';
            echo shell_exec("$ffmpeg  -i ./downloadedVideos/$channelTitle/videoAndAudio/$value[id] -y -qscale 0 ./downloadedVideos/$channelTitle/videoAndAudio/$value[id].mp4 2>&1");
        }
    }
}
echo 'done array of matches 1';
foreach ($arrayOfAlternativeMatches as $key => $value) {
    if (!file_exists("./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id].webm")) {
        echo shell_exec("$youtubedl https://www.youtube.com/watch?v=$value[id] --ffmpeg-location ./ffmpeg  --no-overwrites -f bestaudio[ext=webm]/bestaudio -o ./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id].webm  2>&1");

        $fileInfo = new SplFileInfo("./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id]");
        if ($fileInfo->getExtension() != 'webm') {
            echo 'audio not webm so converting';

            echo shell_exec("$ffmpeg  -i ./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id] -y -qscale 0 ./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id].webm 2>&1");
        }
    }
    if (!file_exists("./downloadedVideos/$channelTitle/videoAndAudio/$value[id].mp4")) {
        echo shell_exec("$youtubedl https://www.youtube.com/watch?v=$value[id] --ffmpeg-location ./ffmpeg --no-overwrites -f bestvideo[ext=mp4]+bestaudio[ext=m4a]/mp4 -o ./downloadedVideos/$channelTitle/videoAndAudio/$value[id].mp4 2>&1");

        $fileInfo = new SplFileInfo("./downloadedVideos/$channelTitle/videoAndAudio/$value[id]");
        if ($fileInfo->getExtension() != 'mp4') {
            echo 'video not mp4 so converting';
            echo shell_exec("$ffmpeg  -i ./downloadedVideos/$channelTitle/videoAndAudio/$value[id] -y -qscale 0 ./downloadedVideos/$channelTitle/videoAndAudio/$value[id].mp4 2>&1");
        }
    }
}

foreach ($arrayOfSubwordMatches as $key => $value) {
    if (!file_exists("./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id].webm")) {
        echo shell_exec("$youtubedl https://www.youtube.com/watch?v=$value[id] --ffmpeg-location ./ffmpeg  --no-overwrites -f bestaudio[ext=webm]/bestaudio -o ./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id].webm  2>&1");

        $fileInfo = new SplFileInfo("./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id]");
        if ($fileInfo->getExtension() != 'webm') {
            echo 'audio not webm so converting';

            echo shell_exec("$ffmpeg  -i ./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id] -y -qscale 0 ./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id].webm 2>&1");
        }
    }
    if (!file_exists("./downloadedVideos/$channelTitle/videoAndAudio/$value[id].mp4")) {
        echo shell_exec("$youtubedl https://www.youtube.com/watch?v=$value[id] --ffmpeg-location ./ffmpeg --no-overwrites -f bestvideo[ext=mp4]+bestaudio[ext=m4a]/mp4 -o ./downloadedVideos/$channelTitle/videoAndAudio/$value[id].mp4 2>&1");

        $fileInfo = new SplFileInfo("./downloadedVideos/$channelTitle/videoAndAudio/$value[id]");
        if ($fileInfo->getExtension() != 'mp4') {
            echo 'video not mp4 so converting';
            echo shell_exec("$ffmpeg  -i ./downloadedVideos/$channelTitle/videoAndAudio/$value[id] -y -qscale 0 ./downloadedVideos/$channelTitle/videoAndAudio/$value[id].mp4 2>&1");
        }
    }
}

foreach ($arrayOfValidSimilarSoundingWordMatches as $key => $value) {
    if (!file_exists("./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id].webm")) {
        echo shell_exec("$youtubedl https://www.youtube.com/watch?v=$value[id] --ffmpeg-location ./ffmpeg  --no-overwrites -f bestaudio[ext=webm]/bestaudio -o ./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id].webm  2>&1");

        $fileInfo = new SplFileInfo("./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id]");
        if ($fileInfo->getExtension() != 'webm') {
            echo 'audio not webm so converting';

            echo shell_exec("$ffmpeg  -i ./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id] -y -qscale 0 ./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id].webm 2>&1");
        }
    }
    if (!file_exists("./downloadedVideos/$channelTitle/videoAndAudio/$value[id].mp4")) {
        echo shell_exec("$youtubedl https://www.youtube.com/watch?v=$value[id] --ffmpeg-location ./ffmpeg --no-overwrites -f bestvideo[ext=mp4]+bestaudio[ext=m4a]/mp4 -o ./downloadedVideos/$channelTitle/videoAndAudio/$value[id].mp4 2>&1");

        $fileInfo = new SplFileInfo("./downloadedVideos/$channelTitle/videoAndAudio/$value[id]");
        if ($fileInfo->getExtension() != 'mp4') {
            echo 'video not mp4 so converting';
            echo shell_exec("$ffmpeg  -i ./downloadedVideos/$channelTitle/videoAndAudio/$value[id] -y -qscale 0 ./downloadedVideos/$channelTitle/videoAndAudio/$value[id].mp4 2>&1");
        }
    }
}
//-----------------------------------------------------------------------------------
//now cut out the individual bits
echo "now cutting out individual bits";
echo shell_exec("rm -R ./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics");
echo shell_exec("rm -R ./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics");

if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/")) {
    mkdir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics");
}
if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/")) {
    mkdir("./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics");
}
foreach ($arrayOfMatches as $key => $value) {
    $substrLyric = substr($value['lyric'], 0, -2);
    if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrLyric")) {
        mkdir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrLyric");
    }
    if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$substrLyric")) {
        mkdir("./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$substrLyric");
    }
    $startTime = max($value[0]["@attributes"]["start"] -$amountToAddToEndAndSubtractFromStartLongClips, 0);
    $duration = $value[0]["@attributes"]["dur"] +$amountToAddToEndAndSubtractFromStartLongClips;
    echo "\n duration: ", $duration;
    echo shell_exec("$ffmpeg -ss $startTime -t $duration -i ./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id].webm -y ./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrLyric/legitPhraseWith*$value[id]*$value[lyric].webm 2>&1");
    echo shell_exec("$ffmpeg -ss $startTime -t $duration -i ./downloadedVideos/$channelTitle/videoAndAudio/$value[id].mp4 -y -crf 20 ./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$substrLyric/legitPhraseWith*$value[id]*$value[lyric].mp4 2>&1");
}

foreach ($arrayOfAlternativeMatches as $key => $value) {
    $substrLyric = substr($value['lyric'], 0, -2);
    if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrLyric")) {
        mkdir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrLyric");
    }
    if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$substrLyric")) {
        mkdir("./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$substrLyric");
    }
    $startTime = max($value[0]["@attributes"]["start"] -$amountToAddToEndAndSubtractFromStartLongClips, 0);
    $duration = $value[0]["@attributes"]["dur"] +$amountToAddToEndAndSubtractFromStartLongClips;

    echo shell_exec("$ffmpeg -ss $startTime -t $duration -i ./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id].webm -y  ./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrLyric/alternativeMatchPhraseWith*$value[id]*$value[lyric]~$value[actual_word].webm 2>&1");
    echo shell_exec("$ffmpeg -ss $startTime -t $duration -i ./downloadedVideos/$channelTitle/videoAndAudio/$value[id].mp4 -y -crf 20 ./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$substrLyric/alternativeMatchPhraseWith*$value[id]*$value[lyric]~$value[actual_word].mp4 2>&1");
}
//now comes the subwords that we need a different way of dealing with
//make a dir inside the main lyric dirs for the subwords

foreach ($arrayOfSubwordMatches as $key => $value) {
    echo 'doing subword matches';
    $substrMainLyric = substr($value['main_lyric'], 0, -2);
    $substrLyric = substr($value['lyric'], 0, -2);

    if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrMainLyric")) {
        mkdir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrMainLyric");
    }


    if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrMainLyric/$substrLyric")) {
        mkdir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrMainLyric/$substrLyric");
    }

    if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$substrMainLyric")) {
        mkdir("./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$substrMainLyric");
    }


    if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$substrMainLyric/$substrLyric")) {
        mkdir("./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$substrMainLyric/$substrLyric");
    }
    $startTime = max($value[0]["@attributes"]["start"] -$amountToAddToEndAndSubtractFromStartLongClips, 0);
    $duration = $value[0]["@attributes"]["dur"] +$amountToAddToEndAndSubtractFromStartLongClips;

    echo shell_exec("$ffmpeg -ss $startTime -t $duration -i ./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id].webm -y  ./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrMainLyric/$substrLyric/subwordPhraseWith*$value[id]*$value[lyric].webm 2>&1");
    echo shell_exec("$ffmpeg -ss $startTime -t $duration -i ./downloadedVideos/$channelTitle/videoAndAudio/$value[id].mp4 -y -crf 20 ./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$substrMainLyric/$substrLyric/subwordPhraseWith*$value[id]*$value[lyric].mp4 2>&1");
}
//
// if(!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrMainLyric/$substrLyric/$value[similar_lyric]")){
// mkdir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrMainLyric/$substrLyric/$value[similar_lyric]");
// }

foreach ($arrayOfValidSimilarSoundingWordMatches as $key => $value) {
    $substrMainLyric = substr($value['main_lyric'], 0, -2);
    $substrLyric = substr($value['sub_lyric'], 0, -2);
    $similarSubStrLyric= substr($value['similar_lyric'], 0, -2);

    echo 'this is substr lyric ;'. $substrLyric;

    if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrMainLyric")) {
        mkdir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrMainLyric");
    }

    if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrMainLyric/$substrLyric")) {
        mkdir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrMainLyric/$substrLyric");
    }

    if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrMainLyric/$substrLyric/$similarSubStrLyric")) {
        mkdir("./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrMainLyric/$substrLyric/$similarSubStrLyric");
    }
    if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$substrMainLyric")) {
        mkdir("./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$substrMainLyric");
    }

    if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$substrMainLyric/$substrLyric")) {
        mkdir("./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$substrMainLyric/$substrLyric");
    }

    if (!is_dir("./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$substrMainLyric/$substrLyric/$similarSubStrLyric")) {
        mkdir("./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$substrMainLyric/$substrLyric/$similarSubStrLyric");
    }
    $startTime = max($value[0]["@attributes"]["start"] -$amountToAddToEndAndSubtractFromStartLongClips, 0);
    $duration = $value[0]["@attributes"]["dur"] +$amountToAddToEndAndSubtractFromStartLongClips;
    echo shell_exec("$ffmpeg -ss $startTime -t $duration -i ./downloadedVideos/$channelTitle/audioOnlyFullVideo/$value[id].webm -y  ./downloadedVideos/$channelTitle/$titleOfSong/audioClipsOfLyrics/$substrMainLyric/$substrLyric/$similarSubStrLyric/similarSubwordPhraseWith*$value[id]*$value[similar_lyric].webm 2>&1");
    echo shell_exec("$ffmpeg -ss $startTime -t $duration -i ./downloadedVideos/$channelTitle/videoAndAudio/$value[id].mp4 -y -crf 20 ./downloadedVideos/$channelTitle/$titleOfSong/videoAndAudioClipsOfLyrics/$substrMainLyric/$substrLyric/$similarSubStrLyric/similarSubwordPhraseWith*$value[id]*$value[similar_lyric].mp4 2>&1");
}

//---------------------------------------------------------------------------------------------
echo("</pre>");
