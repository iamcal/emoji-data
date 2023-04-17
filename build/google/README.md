# Building Android images

## Prerequisites

The Google images are much easier to build these days, but still confusing.
The PNG images are now directly stored in the repo, with the exception of flags, which need to be built.
These steps should allow you to get them building on a completely fresh Ubuntu 22.04 machine.

    # general setup
    sudo apt-get update
    sudo apt install build-essential
    sudo apt install libcairo2-dev libjpeg-dev libgif-dev
    sudo apt install imagemagick

    # build noto-emoji
    cd noto-emoji/
    make flags
    make resized_flags

This will only take a few minutes and will create `build/flags` and `build/resized_flags`.


## Extracting images

After building the flags, you can map all of the images into our naming scheme:

    php map.php

Then you'll want to cut the 64px versions that are used in the sheets:

    ./make64.sh

The resulting 64px images are then ready to use.
