#!/bin/perl

#
# This utility dumps the internal structure of the MORX block,
# to try and give insight into how it's being used as the file
# has evolved.
#

use strict;
use warnings;
use Data::Dumper;
use lib '../lib';
use Font::TTF::Font;
use Font::TTF::Sbix;
use Font::TTF::Morx;
use Font::TTF::TTC;

$Font::TTF::Font::tables{'sbix'} = 'Font::TTF::Sbix';
$Font::TTF::Font::tables{'morx'} = 'Font::TTF::Morx';

my $filename = "apple_color_emoji_11_4.ttc";
$filename = "apple_color_emoji_10_15_1.ttc";

my $f = Font::TTF::TTC->openCollection($filename) || die "Unable to read $filename : $!";
$f->readCollection(0);

my $morx = $f->{'morx'};

$morx->read();

for my $chain_id(0..scalar(@{$morx->{'header'}->{'chains'}})-1){
	print "scanning chain ${chain_id}...\n";

	for my $subtable_id(0..scalar(@{$morx->{'header'}->{'chains'}->[$chain_id]->{'subtables'}})-1){

		my $sub = $morx->{'header'}->{'chains'}->[$chain_id]->{'subtables'}->[$subtable_id];

		print "  subtable ${subtable_id}, type=$sub->{'type'}, length=$sub->{'length'}\n";
	}
}
