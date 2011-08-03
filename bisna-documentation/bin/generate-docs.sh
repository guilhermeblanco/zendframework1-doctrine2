#!/bin/bash
# This script builds the documentation for Bisna
EXECPATH=`dirname $0`
cd $EXECPATH
cd ..

rm -rf html/*
sphinx-build -b html en html
cd html
sed -i '/@import url("basic\.css")/a\@import url\("extra.css"\);'  _static/default.css
