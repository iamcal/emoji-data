#!/bin/perl

use strict;
use warnings;
use Data::Dumper;
use lib '../lib';
use Font::TTF::Font;
use Font::TTF::CBLC;
use Font::TTF::CBDT;

$Font::TTF::Font::tables{'CBLC'} = 'Font::TTF::CBLC';
$Font::TTF::Font::tables{'CBDT'} = 'Font::TTF::CBDT';

my $filename = "NotoColorEmoji_6.0.1_r3.ttf";

my $f = Font::TTF::Font->open($filename) || die "Unable to read $filename";


#
# use the cmap to build a simple mapping of glyph ID -> filename
#

$f->{'cmap'}->read();

my $filenames = {};
for my $tbl (@{$f->{'cmap'}{'Tables'}}){
	for my $uni (keys %{$tbl->{'val'}}){
		my $idx = $tbl->{'val'}{$uni};
		$filenames->{$idx} = sprintf('%04x', $uni);
	}
}


#
# read the GSUB table to build a list of ligatures
#

$f->{'GSUB'}->read();
my $groups = $f->{'GSUB'}{'LOOKUP'}[0]{'SUB'}[0]{'RULES'};
my $cover = $f->{'GSUB'}{'LOOKUP'}[0]{'SUB'}[0]{'COVERAGE'};
my %map;

for my $glyph(keys %{$cover->{'val'}}){
	my $idx = $cover->{'val'}{$glyph};
	for my $row (@{$groups->[$idx]}){

		my $idx2 = $row->{'ACTION'}[0];
		my $name = $filenames->{$glyph};

		for my $uni_idx (@{$row->{'MATCH'}}){
			my $uni = $filenames->{$uni_idx};
			$name .= "-".$uni;
		}

		$filenames->{$idx2} = $name;
	}
}


#
# now read the data chunk and extract the glyphs
#

$f->{'CBLC'}->read();
$f->{'CBDT'}->read();

`rm -f ../../img-google-136/*.png`;

my @keys = keys(%{$f->{'CBDT'}{'bitmap'}[0]});

foreach my $key (@keys){

	my $name = $filenames->{$key} || "unknown_${key}";

	open(my $fh, '>', "../../img-google-136/${name}.png");
	print($fh $f->{'CBDT'}{'bitmap'}[0]{$key}{imageData});
	close($fh);

	print "key $key, $name.png \n";
}


#
# for unknown reasons, the google ligatures are in a different order to the apple ones.
# we'll move them here to be consistent since we want all the filenames to match.
#
# map is google -> apple
#

my $map = {
	'1f466-200d-1f466-200d-1f468-200d-1f468' => '1f468-200d-1f468-200d-1f466-200d-1f466',	# mmbb
	'1f466-200d-1f466-200d-1f469-200d-1f468' => '1f468-200d-1f469-200d-1f466-200d-1f466',	# mwbb
	'1f466-200d-1f466-200d-1f469-200d-1f469' => '1f469-200d-1f469-200d-1f466-200d-1f466',	# wwbb

	'1f466-200d-1f467-200d-1f468-200d-1f468' => '1f468-200d-1f468-200d-1f467-200d-1f466',	# mmbg
	'1f466-200d-1f467-200d-1f469-200d-1f468' => '1f468-200d-1f469-200d-1f467-200d-1f466',	# mwbg
	'1f466-200d-1f467-200d-1f469-200d-1f469' => '1f469-200d-1f469-200d-1f467-200d-1f466',	# wwbg

	'1f467-200d-1f467-200d-1f468-200d-1f468' => '1f468-200d-1f468-200d-1f467-200d-1f467',	# mmgg
	'1f467-200d-1f467-200d-1f469-200d-1f468' => '1f468-200d-1f469-200d-1f467-200d-1f467',	# mwgg
	'1f467-200d-1f467-200d-1f469-200d-1f469' => '1f469-200d-1f469-200d-1f467-200d-1f467',	# wwgg

	'1f466-200d-1f468-200d-1f468' => '1f468-200d-1f468-200d-1f466',	# mm
	'1f466-200d-1f469-200d-1f468' => '1f468-200d-1f469-200d-1f466',	# mw
	'1f466-200d-1f469-200d-1f469' => '1f469-200d-1f469-200d-1f466',	# ww

	'1f467-200d-1f468-200d-1f468' => '1f468-200d-1f468-200d-1f467',	# mm
	'1f467-200d-1f469-200d-1f468' => '1f468-200d-1f469-200d-1f467',	# mw
	'1f467-200d-1f469-200d-1f469' => '1f469-200d-1f469-200d-1f467',	# ww

	'1f468-200d-1f48b-200d-2764-200d-1f468' => '1f468-200d-2764-fe0f-200d-1f48b-200d-1f468',	# m-m-kiss
	'1f469-200d-2764-200d-1f48b-200d-1f468' => '1f468-200d-2764-fe0f-200d-1f48b-200d-1f469',	# m-w-kiss
	'1f469-200d-1f48b-200d-2764-200d-1f469' => '1f469-200d-2764-fe0f-200d-1f48b-200d-1f469',	# w-w-kiss

	'1f468-200d-2764-200d-1f468' => '1f468-200d-2764-fe0f-200d-1f468',	# m-m-heart
	'1f469-200d-2764-200d-1f468' => '1f468-200d-2764-fe0f-200d-1f469',	# m-w-heart
	'1f469-200d-2764-200d-1f469' => '1f469-200d-2764-fe0f-200d-1f469',	# w-w-heart

	
};

for my $google (keys %{$map}){
	`mv ../../img-google-136/${google}.png ../../img-google-136/$map->{$google}.png`;
}


#
# we're still using the old (short) codepoints for family, couple and kiss. copy the images to those names
#

`cp ../../img-google-136/1f468-200d-1f469-200d-1f467-200d-1f466.png     ../../img-google-136/1f46a.png`; # family
`cp ../../img-google-136/1f468-200d-2764-fe0f-200d-1f48b-200d-1f469.png ../../img-google-136/1f48f.png`; # couplekiss
`cp ../../img-google-136/1f468-200d-2764-fe0f-200d-1f469.png            ../../img-google-136/1f491.png`; # couple_with_heart

