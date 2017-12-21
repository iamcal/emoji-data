#!/bin/sh
wget -O unicode/UnicodeData.txt		http://www.unicode.org/Public/10.0.0/ucd/UnicodeData.txt
wget -O unicode/emoji-data.txt		http://www.unicode.org/Public/emoji/5.0/emoji-data.txt
wget -O unicode/emoji-sequences.txt	http://www.unicode.org/Public/emoji/5.0/emoji-sequences.txt
wget -O unicode/emoji-zwj-sequences.txt	http://www.unicode.org/Public/emoji/5.0/emoji-zwj-sequences.txt
wget -O unicode/emoji-test.txt		http://www.unicode.org/Public/emoji/5.0/emoji-test.txt
wget -O unicode/cldr-common.zip		http://www.unicode.org/Public/cldr/31.0.1/cldr-common-31.0.1.zip

cd unicode
unzip cldr-common.zip common/annotationsDerived/en.xml common/annotations/en.xml
