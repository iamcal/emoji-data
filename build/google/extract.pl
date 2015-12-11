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
for my $uni (keys %{$f->{'cmap'}{'Tables'}[1]{'val'}}){
	my $idx = $f->{'cmap'}{'Tables'}[1]{'val'}{$uni};
	$filenames->{$idx} = sprintf('%04x', $uni);
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

		my $uni_a = $filenames->{$glyph};
		my $uni_b = $filenames->{$row->{'MATCH'}[0]};
		my $idx2 = $row->{'ACTION'}[0];

		$filenames->{$idx2} = "${uni_a}-${uni_b}";
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
