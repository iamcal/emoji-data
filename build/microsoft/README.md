# Building the Microsoft emoji images

From any machine:

    php grab.php

This will fetch the 256px versions of all available emoji.

Next you'll want to cut the 64px versions that are used in the sheets:

    php make64.php

The resulting 64px images are then ready to use.
