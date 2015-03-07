#!/bin/perl

use strict;
use warnings;
use Data::Dumper;
use lib 'lib';
use Font::TTF::Font;
use Font::TTF::CBLC;
use Font::TTF::CBDT;

$Font::TTF::Font::tables{'CBLC'} = 'Font::TTF::CBLC';
$Font::TTF::Font::tables{'CBDT'} = 'Font::TTF::CBDT';

my $filename = "NotoColorEmoji_lollipop.ttf";

my $f = Font::TTF::Font->open($filename) || die "Unable to read $filename";

$f->{'cmap'}->read();
my @map = $f->{'cmap'}->reverse();

$f->{'CBLC'}->read();
$f->{'CBDT'}->read();

my @keys = keys(%{$f->{'CBDT'}{'bitmap'}[0]});

foreach my $key (@keys){

	my $uni = $map[$key];
	my $flat = $uni ? sprintf('%04x', $uni) : "unknown_${key}";

	open(my $fh, '>', "images/${flat}.png");
	print($fh $f->{'CBDT'}{'bitmap'}[0]{$key}{imageData});
	close($fh);

	print "key $key, $flat \n";
}
