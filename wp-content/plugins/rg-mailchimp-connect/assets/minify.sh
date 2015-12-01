#!/bin/bash

# This is just a simple wrapper around the YUI compressor.  It assumes you have a Java executeable installed.
#
# More info on the YUI Compressor can be obtained from here: http://yui.github.io/yuicompressor/

if [ ! -n "$1" ]; then
    echo "Must supply a file to minify!"
    echo "Usage: ./minify.sh [filename.js]"
    exit 1
fi

# From StackOverflow: http://stackoverflow.com/questions/965053/extract-filename-and-extension-in-bash
filename=$(basename "$1")
extension="${filename##*.}"
filename="${filename%.*}"

if [ $extension != "js" ]; then
	echo "File extension must be .js"
	exit 1
fi

min_name="${filename}.min.${extension}"

# Run it!
java -jar yuicompressor-2.4.8.jar ${1} -o ${min_name}
