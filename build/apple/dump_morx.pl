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

&scan_tables("apple_color_emoji_10_15_1.ttc");
&scan_tables("apple_color_emoji_11_4.ttc");


sub scan_tables {

	my ($filename) = @_;

	my $f = Font::TTF::TTC->openCollection($filename) || die "Unable to read $filename : $!";
	$f->readCollection(0);

	my $morx = $f->{'morx'};
	$morx->read();

	my $sizes = {};

	for my $chain_id(0..scalar(@{$morx->{'header'}->{'chains'}})-1){
		#print "scanning chain ${chain_id}...\n";

		for my $subtable_id(0..scalar(@{$morx->{'header'}->{'chains'}->[$chain_id]->{'subtables'}})-1){

			my $sub = $morx->{'header'}->{'chains'}->[$chain_id]->{'subtables'}->[$subtable_id];

			#print "  subtable ${subtable_id}, type=$sub->{'type'}, length=$sub->{'length'}\n";

			$sizes->{$sub->{'type'}} += $sub->{'length'};

			print "subtable ${subtable_id}, type=$sub->{'type'}, coverage=".sprintf('%08x', $sub->{'coverage'}).", subFeatureFlags=".sprintf('%08x', $sub->{'subFeatureFlags'})."\n";
		}
	}

	#print Dumper $sizes;

	print "$filename\n";
	print "    0: (Indic) Rearrangement subtable -> $sizes->{0}\n" if $sizes->{0};
	print "    1: Contextual subtable            -> $sizes->{1}\n" if $sizes->{1};
	print "    2: Ligature subtable              -> $sizes->{2}\n" if $sizes->{2};
	print "    3: RESERVED subtable              -> $sizes->{3}\n" if $sizes->{3};
	print "    4: Noncontextual (Swash) subtable -> $sizes->{4}\n" if $sizes->{4};
	print "    5: Insertion subtable             -> $sizes->{5}\n" if $sizes->{5};
	print "\n";

}
