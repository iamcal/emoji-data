## Install the tools

Do all of this from your home dir:

    apt-get install zopfli

    wget https://github.com/amadvance/advancecomp/releases/download/v1.20/advancecomp-1.20.tar.gz
    tar xzf advancecomp-1.20.tar.gz
    cd advancecomp-1.20/
    ./configure
    make
    make install
    cd ..
    rm -rf advancecomp-1.20*

    wget -Opngcrush-1.8.1.tar.gz "http://downloads.sourceforge.net/project/pmt/pngcrush/1.8.1/pngcrush-1.8.1.tar.gz?r=https%3A%2F%2Fsourceforge.net%2Fprojects%2Fpmt%2Ffiles%2Fpngcrush%2F1.8.1%2F&ts=1465432592&use_mirror=tenet"
    tar xzf pngcrush-1.8.1.tar.gz
    cd pngcrush-1.8.1/
    make
    cp pngcrush /usr/local/bin/
    rm -rf pngcrush-1.8.1*

    wget http://static.jonof.id.au/dl/kenutils/pngout-20150319-linux-static.tar.gz
    tar xzf pngout-20150319-linux-static.tar.gz
    cp pngout-20150319-linux-static/x86_64/pngout-static /usr/local/bin
    rm -rf pngout-20150319-linux-static*

    git clone https://github.com/subzey/zopfli-png.git

    wget -Ooptipng-0.7.6.tar.gz "http://downloads.sourceforge.net/project/optipng/OptiPNG/optipng-0.7.6/optipng-0.7.6.tar.gz?r=https%3A%2F%2Fsourceforge.net%2Fprojects%2Foptipng%2Ffiles%2FOptiPNG%2Foptipng-0.7.6%2F&ts=1465493988&use_mirror=heanet"
    tar xzf optipng-0.7.6.tar.gz
    cd optipng-0.7.6
    ./configure
    make
    make install
    cd ..
    rm -rf optipng-0.7.6*
