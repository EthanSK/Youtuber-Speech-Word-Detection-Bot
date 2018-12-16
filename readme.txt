Instructions:

index.php is the start file that the server runs. It contains links to all the other files in the directory (folder)

The initialising variables are at the beginning of the index. Don't set $numberOfTimesToGetSameWord too high (6 or 7 upper limit is sensible) or it will take forever and use up loads of watson api minutes

Make sure the $usernameWatson and password variables are set to your watson speech to text api service credentials that you generated. if the bot gives Internal Server Error - Write, it means you have run out of minutes becaues your plan is on lite not standard.

set $titleOfSong and $channelTitle with no spaces and no special characters like apostrophe's

There is a songLyrics folder, where text files of all the songs go. Make sure the name of the file is the same as the $channelTitle

Get the channel id of the youtuber (the one that looks like UCS5Oz6CHmeoF7vSad0qqXfw not some custom channel id) and set the $channelToGetVidsFrom

$maxLengthOfVideoAllowed is the most number of seconds a video can be to be used in the bot, 900 seconds seems pretty reasonable

$numberOfVideosToGetCaptionsFrom is the number of videos to get from the channel (gets most recent ones)

If you want to provide a text file of youtube videos, there is a file called videoURLsToUse.txt, put each video link line by line, and then in index.php set the $howAreWeGettingTheVideos = 2

$amountToAddToEndAndSubtractFromStart is the amount of extra time in seconds to add to the start and end of the final word (currently at 1 second which is pretty reasonable)





to start the bot:
open xampp (manager-osx), (it should be running php 5.6.3 btw), go to manage servers, and start the apache web server
then, open a web browser, ideally chrome since it deals with large amounts of text pretty decently, and go to http://localhost/speechbot/
leave it to run, it should take a long time, depending on what settings you used it can be a few hours to overnight
