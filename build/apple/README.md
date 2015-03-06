# Building the Apple emoji images

On a Mac running OS X:

    gem install bundler
    bundler
    ./extract_images.sh

This will populate the latest image files into `../../img-apple-160`.

Next you'll want to cut the 64px versions that are used in the sheets"

    php make64.php

And finally optimize them. This requires OptiPNG to be installed (http://optipng.sourceforge.net/)
and takes a very long time:

    optipng -o7 ../../img-apple-64/*

The resulting 64px images are then ready to use.


## Catalog

On a Mac running OS X you can also extract the latest version of the emoji categorization:

    ./extract_categories.sh

