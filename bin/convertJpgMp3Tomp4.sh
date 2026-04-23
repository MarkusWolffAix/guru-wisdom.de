#!/bin/zsh
mpath=/Users/markuswolff/Downloads
ffmpeg -loop 1 -i $mpath/$1.jpg -i $mpath/$1.mp3 -vf "scale=trunc(iw/2)*2:trunc(ih/2)*2,format=yuv420p" -c:v libx264 -tune stillimage -c:a aac -b:a 192k -shortest $mpath/$1.mp4 

