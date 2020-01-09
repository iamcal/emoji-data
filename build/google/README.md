# Building Android images


## Prerequisites

You'll need to have python's fontTools installed to get started:

    apt-get update
    apt-get install -y python3-pip build-essential
    apt-get install -y pkg-config libcairo2-dev
    pip3 install fonttools

Installing nototools is a bit more involved:

    git clone https://github.com/googlei18n/nototools.git
    cd nototools/
    pip3 install -r requirements.txt
    python3 setup.py install

You will also need to have already installed zopflipng/optipng/pngquant.


## Extracting images

Build the original 136px emoji images:

    ./make136.sh

Copy them into our naming scheme:

    php map136.php

Then you'll want to cut the 64px versions that are used in the sheets:

    ./make64.sh

The resulting 64px images are then ready to use.
