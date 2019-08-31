I remade the bot to be cross platform, have a nice GUI, and just be better overall: https://www.etggames.com/youtube-word-finder



# Celebrity/Youtuber Sings Speech/Word Detection Bot

## Intro

Before explaining how the bot works, lemme show you what the bot lets you do. You know those videos that makes certain celebrities/youtubers sing by stitching together instances of each word in the lyrics of a song together? Well it makes creating videos like that, for example the ones on this channel, SUPER EASY!!:
https://www.youtube.com/channel/UCtvbvzHVrc3SUqQerG1cFGA  

These ones I actually personally edited for the channel:
https://www.youtube.com/watch?v=YKq5wwtvCyc 
https://www.youtube.com/watch?v=TQkunqJX3rw 
https://www.youtube.com/watch?v=2ZriTDY0zeE 
https://www.youtube.com/watch?v=1BzqGwtBOu0 
https://www.youtube.com/watch?v=yn5227Fhu10 

In fact, this very channel USES THIS VERY BOT!!! I have been collaborating extensively with the owner, and helped him set it up on his machine. However, I have realised that this project is too awesome to be kept private, and used only by one channel. I'm not even profiting from it (like I originally hoped :p)


## OKEH, but what does it actually DO?

Now y'all prolly sitting there thinking: "Damn, the owners of these channels just sit there for hours and hours watching loads of videos trying to find occurences of when the celebrity says the word they're looking for. How sad; have they nothing better to do???"

Well my son, you have a lot to learn. Not. Because that's exactly what they do, and its an extremely daunting asf process. Seems rather repetitive...but wait...isn't repetition what computers are good at?? Yes. yes.

So yeah, this bot I made goes and does that for them 😱😱😱 It scans a bunch of videos on a given youtube channel / given youtube api search query, uses mAcHiNe LeArNiNg to detect when certain words are spoken in those videos, then uses the beautiful ffmpeg to cut those parts from those videos and puts them neatly in an organised filesystem. Then you can easily drag and drop those clips containing each lyric into a video editor and create your song!

Here is an example of said output for Liza Koshy singing Fireflies by Owl Nation:
https://drive.google.com/file/d/0B89Pkf0QGGTOQXlMWTVIc2VkY1k/view  
(Big download, 3.5 GB, contains bunch of video files)

You can init the settings for the method to get the videos to scan, number of times to get the same word (in case one instance of the result is slightly wrong), max length of videos to use for scanning, the length of padding to add to the start and end of each final video clip for easy editing, and a whole lot more...

I went one step further, and used an autotune api to try and match each video clip of a lyric to the actual pitch in the actual song, so it sounds even more similar to the original song and sounds like actual singing instead of robotic rapping!

And believe it or not, I went EVEN further, and extended this bot's functionality to auto EDIT the videos!?!?! wtf?? Isn't that like retardedly stupid and insane? Yeh
Basically, you have to give it a subtitle file containing the time at which each lyric appears in the actual song, and how long it lasts, which is maybe a long-ish process to do for each song the first time, but then you can reuse that file if you are using the same song but with different celebrities...

The way it does this automated editing is by selected the most confident (autotuned) video clip for each lyric, stretching it or squishing it to be the exact time of the lyric in the actual song, then placing each result in an ordered folder of final lyrics. Then it can hopefully be dragged into a video editor, and all you have to do is drag in the instrumental of the song, and the lyrics should line up!

Now in practice, this works ok-ish, surprisingly good considering how drop dead mad this whole thing is.  

Sounds too good to be true?

#### The catch

I made this on a mac, and it has only been tested on XAMPP with PHP 5.6.3*
Additionally, all the command line commands are done in bash. So defo won't work on Windows. Maybe Linux tho.
Anyway, macos is the superior os, you windows plebs can suffer and learn to get a mac the hard way.
So, not much of a catch.

## Technical overview

The bot can take quite a while to run. Maybe the whole night, depending on the settings you give it. Don't run it on a potato. It can run on a virtual machine with 4 gb of ram I think. You need decent wifi speed and space on ur storage to download dem juicy videos. otherwise set number of videos to use to something lower.

At the top of index.php are some useful comments as to what each variable setting does. 

You have to give the bot a text file of lyrics for the song you want, and change the title, and channel etc from inside the index.php file

What happens is the bot will scan the youtube video for subtitles, both auto generated and manual, and try and get some timing data for particular phrases (groups of words). It will then use the youtube-dl tool to download the necessary videos. 

It sends the phrases off with the timing data to IBM Watson speech to text API, searches for given keywords from the user input lyrics file and returns loads of data containing the exact precise start and end times of each word. It also searches for similar sounding words if the actual word is not found, and it searches for sub words that constitute a full word, details further down.

It then will parse all that data and use ffmpeg to chop up the videos into final usable clips.

With the autotune and autoediting stuff, I have made it work in the past correctly, and haven't changed the code since then, but I don't know if it'll work and i'm a lazy boi and cba to test it and make sure it is fully working, so it is left as an exercise to the reader to prove and correct the code in the form of a pull request :p

## Brief instructions

Before you run you need composer and run composer install in the current working directory of the project.

The bot shouldn't be too hard to figure out how to run. Just use xampp with php 5.6.3 on a mac, drag this root dir into htdocs in the xampp folder in applications, start the apache server (manage servers > bottom button of vertical list of buttons(text doesn't appear in new macos ffs)), wait till server is on and green icon shows, go to 127.0.0.1/*insert_rootdir_name* in a good browser like chrome (sometimes you must do additional things with the php config files in xampp to allow the bot to run for longer than the preset timeout - just look up how to do this its easy and u just change 1 line i think.). Also give the entire root dir recursive read write permissions for all users.

You need youtube api credentials. In teh youtubeDataGetter, change these lines:

$OAUTH2_CLIENT_ID = 'you have';  
$OAUTH2_CLIENT_SECRET = 'now encountered';  
$refreshToken = 'god himself';  

to your correct values.

You must create a watson api account and put in your credentials in the index.php file. There is a free tier up until a point, so don't make the settings too insane in the index.php.

Put the lyrics in ./songLyrics/*name of song with no spaces or special characters pls*.txt


## How to interpret the results

btw, pdp = PewDiePie, which is a youtube channel name

- in the orderedTrimmedFinalVideo folder you will find a bunch of folders. You can probs see its an ordered list of all the lyrics in the song, with repeating words to make it easier to edit, you just go to the next folder for the next word

- inside each lyric folder you will find mp4 files and other folders. The mp4 files are either occurrences of the whole lyric being said in a pewdiepie transcript., or the lyric inside a BIGGER word from the pewdiepie transcript. Confusing? For example, say the pdp transcript was ‘you are such a great youtuber’, then the mp4 files I was just describing would either cut the first word, you, from the transcript, or it would cut the word ‘youtuber’ since you is in youtuber

- if there is a folder inside the lyric folder, for example in this case it’s fireflies, because my bot did not find pdp saying fireflies in any of his 3k + videos (!!!), then it will use the dictionary text file i talked about in the prev email to find pdp saying parts of the word fireflies, for example, ‘fire’, ‘flies’ , ‘lie’, ‘lies’ and ‘ref’. Now inside THESE folders you can yet again find an mp4 file or yet another folder…

- if you do find yet another folder, (last folder inception i promise), then this will be a similar sounding word to the sub word, so if there were another folder inside fire, from the example above, then this would be a word that sounds like fire, and inside that folder would be an mp4 file, not another folder. However, in this example, there is no similar word to fire, or it managed to find ‘fire’, ‘flies’, ‘lie’ etc and didnt have to search for similar sounding words. An example where there is a similar sounding subword is the lyric ‘lightning’, in the folder 80_lightning. You see it found one occurance of llightning and saved it as an mp4, but the rest are more folders. If you go into ‘lig’, you will see another folder, ‘leg’, since leg sounds similar to lig. Now i know its not perfect, you will find many cases where this is useless or there may not be mp4s in the folder, but ill get onto that. 

- Now you may be thinking, ethan, why is there only one lightning mp4 file, i’m pretty sure pdp has said lightning more than once before?? well he has, but  only words that watson speech to text api actually detect (with glitches, the tech isnt perfect its only 2017 m9) actually get put in the file. If you go into the videoAndAudioClipsOfLyrics folder which is in the same level as the orderedTrimmedFinalVideo folder, you will see all the phrases containing each lyric, but not ordered, and each one only appears once. For this example, go into the lightning folder in videoAndAudioClipsOfLyrics folder, and you will see 2 mp4 files with some other folders. Notice how one of these mp4 files didnt make it into the final orderedTrimmedFinalVideo folder. Listen through both of them. In one of them, pdp never eve said the word lightning, but then how did it appear in the subtitle transcript? Well there is a lightning sound effect, and  the subtitle probably contained something like *lightning in the background*. This is one reason why I use watson text to speech, to make sure they actually say the words.

- However, sometimes watson isnt perfect, and so you may have to scan through this videoAndAudioClipsOfLyrics folder if there are not enough or not good enough lyrics found and put in orderedTrimmedFinalVideo, but this should be very infrequent, maybe 2 or 3 times in total. Another reason I use watson is coz it finds the keywords’ for example in the transcript ‘go make me a sandwich’, it will find the keyword ‘make’ with the start and end time and confidence of how accurate it is. This obviously allows me to cut the specific word from the long phrase, but it also allows me to place the final lyrics found in order of confidence in orderedTrimmedFinalVideo. For example, in orderedTrimmedFinalVideo/0_you, there will be 5 mp4’s called you-n.mp4. the lower n is, the more confident watson detected that word. So unless you can hear otherwise, if you do not know which clip to use, always go for the one with the lowest number n. Eg watson thinks its better to use you-0.mp4 than you-1.mp4 (but irl it may not be the case, you have to decide at the end of the day). 

- There will obviously be glitches and weird things u may not expect, when I made my version of pdp sings fireflies it didn’t present too much of a problem considering I made it try to get like 6 occurances of each lyric, but make sure to message me if something is unusable or if you have any questions about how it works!

- I added a feature so that the number after the word tells you how many times that word has appeared before, so for example if the folder was called 30_potato_5, the 30 means it is the 3rd lyric in the song, and the 5 means potato has occurred 5 times before

- If you do not want to pay for watson to get more accurate and finer results, you can just use the clips stored in  ./downloadedVideos/PewDiePie/GodsPlan/videoAndAudioClipsOfLyrics or the equivalent. It is still pretty good, just the clips are slightly longer, the length of the entire phrase, so like 5 seconds or so


### An explanation I wrote ages ago no. 1

This could be helpful if you want to figure out more as to how the bot works:

It reads the subtitle files to find the general whereabouts of the words. Then it downloads the videos where those words were found. Then it cuts that video to the ‘phrase’ that word was found in, because as you may already know the subtitle timings are not for each word, but rather for a phrase. Then it sends those chopped up clips through IBM’s watson speech to text api. This allows it to not only determine the ‘confidence’ of each word, but also the exact start and end times of the word. 

For youtube subtitles, sometimes they are human generated, but other times they are auto generated. The bot can deal with both, but when they are auto generated, the fact that the clips go through watson api ensure that I get best results and accuracy. There are some minor hiccups, but none that actually affect you in the end, since there are always like 6 or 7 (i can change this setting) words to choose from.

### An explanation I wrote ages ago no. 2

I have this readme.txt file in the root dir that reads as follows:


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

Kinda useful stuff, reiterating a bit tho


## Conclusion

You need some technical knowledge and experience with php and the environment, but I know people w/o any and still set it up with some help. ANY questions please open an issue and I will try to answer asap.

Sorry for the shitty code quality and reusability...I used to be dum when i made this back in the day...at least the names are...informative :)
