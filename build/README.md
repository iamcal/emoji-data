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
	./quant_sheets.sh (~2 mins)

	parallel ./optimize.sh ::: ../sheet_*.png (~18 mins)
	parallel ./optimize.sh ::: ../sheets-*/*.png (~25 mins)
	parallel ./optimize.sh ::: ../img-*-64/* (~92 mins)


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
* Update images for all images set (see READMEs in theri sub-dirs)
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


## Install the optimization tools

We need newer versions of everything than e.g. Debian has:

    wget https://github.com/amadvance/advancecomp/releases/download/v1.20/advancecomp-1.20.tar.gz
    tar xzf advancecomp-1.20.tar.gz
    cd advancecomp-1.20/
    ./configure
    make
    sudo make install
    cd ..
    rm -rf advancecomp-1.20*

    wget -Opngcrush-1.8.13.tar.gz "https://downloads.sourceforge.net/project/pmt/pngcrush/1.8.13/pngcrush-1.8.13.tar.gz?r=https%3A%2F%2Fsourceforge.net%2Fprojects%2Fpmt%2Ffiles%2Fpngcrush%2F1.8.13%2Fpngcrush-1.8.13.tar.gz%2Fdownload&ts=1558118588"
    tar xzf pngcrush-1.8.13.tar.gz
    cd pngcrush-1.8.13/
    make
    sudo cp pngcrush /usr/local/bin/
    cd ..
    rm -rf pngcrush-1.8.13*

    wget http://static.jonof.id.au/dl/kenutils/pngout-20150319-linux-static.tar.gz
    tar xzf pngout-20150319-linux-static.tar.gz
    sudo cp pngout-20150319-linux-static/x86_64/pngout-static /usr/local/bin
    rm -rf pngout-20150319-linux-static*

    wget -Ooptipng-0.7.6.tar.gz "http://downloads.sourceforge.net/project/optipng/OptiPNG/optipng-0.7.6/optipng-0.7.6.tar.gz?r=https%3A%2F%2Fsourceforge.net%2Fprojects%2Foptipng%2Ffiles%2FOptiPNG%2Foptipng-0.7.6%2F&ts=1465493988&use_mirror=heanet"
    tar xzf optipng-0.7.6.tar.gz
    cd optipng-0.7.6
    ./configure
    make
    sudo make install
    cd ..
    rm -rf optipng-0.7.6*

    wget https://download.sourceforge.net/libpng/libpng-1.6.37.tar.gz
    tar xzf libpng-1.6.37.tar.gz
    cd libpng-1.6.37/
    ./configure
    make --prefix=/usr
    sudo make install
    cd ../
    rm -rf libpng-1.6.37

    git clone git://github.com/pornel/pngquant.git
    cd pngquant/
    ./configure
    make
    sudo make install
    cd ..
    rm -rf pngquant

    git clone https://github.com/google/zopfli.git
    cd zopfli
    make zopflipng
    sudo cp zopflipng /usr/local/bin
    cd ..
    rm -rf zopfli

## Setting up a fresh Amazon Linux instance to process the images

Since it requires some time and a lot of CPU & memory, I usually do it in EC2:

    sudo yum install -y git
    sudo yum install -y php
    sudo yum install -y ImageMagick
    sudo yum install -y gcc
    sudo yum install -y gcc-c++
    sudo yum install -y zlib-devel
    sudo yum install -y parallel
