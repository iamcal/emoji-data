# Building Android images

## Prerequisites

The Google images are notoriously difficult to build.
The toolchain requires python 3.7, which must be the version that `python3` points to.
These steps should allow you to get them building on a completely fresh Ubuntu 20.04 machine.

    # general setup
    sudo apt-get update
    sudo apt-get upgrade
    sudo apt-get install -y python3-venv python3-pip zopfli libcairo2-dev imagemagick pngquant

    # setup nototools
    cd
    git clone https://github.com/googlei18n/nototools.git
    cd nototools/
    git checkout v0.2.13
    pip3 install launchpadlib
    pip3 install -r requirements.txt
    sudo python3 setup.py install

    # build noto-emoji
    cd
    git clone https://github.com/googlefonts/noto-emoji.git
    cd noto-emoji/
    python3 -m venv venv
    source venv/bin/activate
    pip3 install -r requirements.txt
    time make -j

The final command will take quite a while to run (15 minutes on a large EC2 instance).
You can list files inside `build/compressed_pngs/` to check progress; there are 3,573 in the 13.0 version.


## Extracting images

After running the makefile successfully, copy over the original 136px emoji images:

    ./make136.sh

Copy them into our naming scheme:

    php map136.php

Then you'll want to cut the 64px versions that are used in the sheets:

    ./make64.sh

The resulting 64px images are then ready to use.
