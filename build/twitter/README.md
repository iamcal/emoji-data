# Building the Twitter emoji images

From any machine:

    php grab.php

This will fetch the 72px versions of all available emoji.

Next you'll want to cut the 64px versions that are used in the sheets:

    php make64.php

And finally optimize them. This requires OptiPNG to be installed (http://optipng.sourceforge.net/)
and takes a very long time:

    optipng -o7 ../../img-twitter-64/*

The resulting 64px images are then ready to use.
