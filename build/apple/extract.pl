#!/bin/perl

use strict;
use warnings;
use Data::Dumper;
use lib '../lib';
use Font::TTF::Font;
use Font::TTF::Sbix;

$Font::TTF::Font::tables{'sbix'} = 'Font::TTF::Sbix';

my $filename = "apple_color_emoji_10_10_3.ttf";

my $f = Font::TTF::Font->open($filename) || die "Unable to read $filename : $!";


#
# we ultimately want to build a mapping of glyph_id -> $filename
#

my $filenames = {};


#
# use the cmap to build a simple mapping
#

if (1){
	$f->{'cmap'}->read();

	for my $uni (keys %{$f->{'cmap'}{'Tables'}[0]{'val'}}){
		my $idx = $f->{'cmap'}{'Tables'}[0]{'val'}{$uni};
		$filenames->{$idx} = sprintf('%04x', $uni).'.png';
	}

	#print Dumper $filenames;
}


#
# write out glyphs
#

$f->{'sbix'}->read();

for my $glyph_id(keys %{$filenames}){
	my $filename = $filenames->{$glyph_id};
	my $strike = $f->{'sbix'}->read_strike(160, 0 + $glyph_id, 1);
	if ($strike->{'graphicType'} eq 'png '){
		# all good - save it
		print "ok - $filename\n";

		open(my $fh, '>', "../../img-apple-160-perl/$filename");
		print($fh  $strike->{'data'});
		close($fh);
	}else{
		# something we don't expect
		print "unexpected glyph type ($strike->{'graphicType'}) for index $glyph_id (filename $filename)\n";
	}
}

#$f->{'sbix'}->read();
#print Dumper $f->{'sbix'};

#my $strike = $f->{'sbix'}->read_strike(160, 505);
#print Dumper $f->{'sbix'};
#print Dumper $strike;

#$f->{'maxp'}->read();
#print Dumper $f->{'maxp'};
