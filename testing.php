<?php
$titleOfSong = 'Fireflies';
$channelTitle = "PostpartumDepression";
$youtubedl = './youtube-dl';
 $shouldSeparateFinalAudioAndVideo= 1;
echo("<pre>");
ini_set('memory_limit', '1024M');
error_reporting(E_ALL ^ E_NOTICE);
require_once('functions.php');
$titleOfSong = 'Fireflies';//needed for all
$channelTitle = "Postpartum Depression";
$channelTitle = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $channelTitle);
$titleOfSong = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $titleOfSong);
//---------------------------------------------------------------------------------------------
mkdir("./test/test/asoeuth/aosentuh");
