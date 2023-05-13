## Building the data

The scripts in this `build/` sub directory allow you to rebuild the data files 
from a mix of sources, including unicode.org standards and the gemoji project.

You can rebuild by following these steps:

	cd build/

	# Rebuild catalog.php from the original data table (full.html)
	php build_catalog.php full.html > catalog.php

	# Rebuild the master mapping
	./download_spec_files.sh
	php build_map.php

	# From the final mapping, build a preview table
	php build_table.php > ../table.htm

	# Rebuild positions and make the master spritesheets
	# This step requires ImageMagick or GraphicsMagick. Versions of ImageMagick
	# before 6.7 will build the images in an incorrect order, so make sure to
	# update!
	php build_image.php

	# create quantized sheets and optimize them all
	time ./quant_sheets.sh (~1 min)

	time parallel ./optimize.sh ::: ../sheet_*.png (~19 mins)
	time parallel ./optimize.sh ::: ../sheets-*/*.png (~25 mins)
	time parallel ./optimize.sh ::: ../img-*-64/* (~95 mins)


To find out how to extract the original source glyphs, look inside the sub
directories under `build/`.

## Upgrading to a new version of the Unicode & Emoji standard

* Update `./download_spec_files.sh` to point to the latest source files, then run it
* Edit `find_added_in_version_names.php` to point to the version you care about, then generate names, e.g. `php find_added_in_version_names.php > data_emoji_names_v13.txt`
* Manually edit the names file to use the expansions for gender and skin tone(s)
* Edit `build_map.php` to load the new names list
* Run `build_map.php` and check for missing names. Errors look like this:
    `Found sequence not supported: 1f935-1f3fb-200d-2642-fe0f / E13.0  [1]`
* Run `build_map.php` to generate a new catalog
* Update images for all images set (see READMEs in their sub-dirs)
* Re-run `build_map.php` and then follow the rest of the build steps


## Cutting a new release

1. Land new commits onto master
2. Update `CHANGES.md` with version history
3. Update `package.json` with new version number (now in only 1 place)
4. Update `README.md` with the correct Unicode/Emoji version
4. Update `README.md` and `CHANGES.md` with the correct source versions for all image sets
5. Add a git tag
6. Publish to npm: `php npm_prep.php` and `php npm_publish.php`
7. Update downstream libraries


## Setting up a fresh VM to process the images

Since it requires some time and a lot of CPU & memory, I usually spin up a temporary EC2 instance for image optimization.
As of Ubuntu 22.04, you can install recent versions of all dependencies via apt:

    sudo apt-get update
    sudo apt-get install -y git php imagemagick parallel
    sudo apt-get install -y pngquant zopfli

Don't forget to set up your git config before committing anything!

    git config --global user.name "Cal Henderson"
    git config --global user.email "cal@iamcal.com"
