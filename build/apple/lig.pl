#!/bin/perl

use strict;
use warnings;
use Data::Dumper;
use lib '../lib';
use Font::TTF::Font;
use Font::TTF::Sbix;
use Font::TTF::Morx;

$Font::TTF::Font::tables{'sbix'} = 'Font::TTF::Sbix';
$Font::TTF::Font::tables{'morx'} = 'Font::TTF::Morx';

my $filename = "apple_color_emoji_10_10_3.ttf";

my $f = Font::TTF::Font->open($filename) || die "Unable to read $filename : $!";


#

$f->{'morx'}->read();

#print $f->{'morx'}->resolve_ligature([0x1f1ea, 0x1f1f8])."\n"; # spanish flag
#print $f->{'morx'}->resolve_ligature([0x1f1ea, 0x1f1f9])."\n"; # some other flag
#print $f->{'morx'}->resolve_ligature([0x1f3ca, 0x1f3ff])."\n"; # swimmer, black skin

print $f->{'morx'}->resolve_ligature([0x1f469, 0x200d, 0x1f469, 0x200d, 0x1f467])."\n";
print $f->{'morx'}->resolve_ligature([0x1f469, 0x200d, 0x1f469, 0x200d, 0x1f467, 0x200d, 0x1f466])."\n";
