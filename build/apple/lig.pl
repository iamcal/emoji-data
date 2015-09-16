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

$f->{'morx'}->resolve_ligature([0x1f1ea, 0x1f1f8]);

exit;


print scalar @{$f->{'morx'}->{'header'}->{'chains'}};

exit;

#resolve_ligature

my $tbl = $f->{'morx'}->{'header'}->{'chains'}->[0]->{'subtables'}[1];

print Dumper $tbl;

for my $i(0..scalar(@{$tbl->{states}})-1){
	my $h = sprintf('%04x', $tbl->{states}->[$i]->[1]);
	print "$i : [$tbl->{states}->[$i]->[0], 0x$h, $tbl->{states}->[$i]->[2]]\n";
}

