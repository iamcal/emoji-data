#!/bin/perl

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
#$filename = "apple_color_emoji_10_15_5.ttc";

my $f = Font::TTF::TTC->openCollection($filename) || die "Unable to read $filename : $!";
$f->readCollection(0);

#

$f->{'morx'}->read();

#print $f->{'morx'}->resolve_ligature([0x1f1ea, 0x1f1f8])."\n"; # spanish flag
#print $f->{'morx'}->resolve_ligature([0x1f1ea, 0x1f1f9])."\n"; # some other flag
#print $f->{'morx'}->resolve_ligature([0x1f3ca, 0x1f3ff])."\n"; # swimmer, black skin

#print $f->{'morx'}->resolve_ligature([0x1f469, 0x200d, 0x1f469, 0x200d, 0x1f467])."\n";
#print $f->{'morx'}->resolve_ligature([0x1f469, 0x200d, 0x1f469, 0x200d, 0x1f467, 0x200d, 0x1f466])."\n";

print Dumper $f->{'morx'}->resolve_ligature([0x1F468, 0x200D, 0x2764, 0xFE0F, 0x200D, 0x1F468]); # man-heart-man
