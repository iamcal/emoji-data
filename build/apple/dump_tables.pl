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

	print "$filename\n";
	$f->tables_do(sub{
		my ($table, $name) = @_;
		print "  $name -> $table->{' LENGTH'}\n";
	});

}
