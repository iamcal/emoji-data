# Building the EmojiOne images

Copy the files into place:

    php get64.php

And finally optimize them. This requires the optimizing tools in `build/README.md` to be installed
and takes a very long time:

    ../optimize.sh ../../img-emojione-64/*

The resulting 64px images are then ready to use.
