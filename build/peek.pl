#!/usr/bin/perl

use warnings;
use strict;

open F, $ARGV[0] or die $!;
my $junk;
my $buffer;
read F, $junk, 16;
read F, $buffer, 10;

my ($w, $h, $depth, $type) = unpack('NNCC', $buffer);

print "\tSize  : $w x $h\n";
print "\tType  : $type\n";
print "\tDepth : $depth\n";

