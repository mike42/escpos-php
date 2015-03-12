#!/bin/sh
for i in png jpg gif bmp; do
	for j in white black; do
		convert -size 1x1 canvas:$j canvas_$j.$i
	done
done
