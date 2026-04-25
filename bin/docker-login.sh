#!/bin/bash
BASE=/Users/markuswolff/Development/guru-wisdom.de
grep GITHUB_TOKEN $BASE/.env | cut -d '=' -f2 | docker login ghcr.io -u MarkusWolffAix --password-stdin
