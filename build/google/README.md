# Building Android images

Build the original 136px emoji images:

    ./make136.sh

Next you'll want to cut the 64px versions that are used in the sheets:

    ./make64.sh

And finally optimize them. This requires the optimizing tools in `build/README.md` to be installed
and takes a very long time:

    ../optimize.sh ../../img-google-64/*

The resulting 64px images are then ready to use.
