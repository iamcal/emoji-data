#!/usr/bin/perl

use strict;
use warnings;
use Data::Dumper;

my $stateArray = [
	[0,0,0,0,2,0,3,4,5,6,7,8,9,10,11,12],
	[0,0,0,0,2,0,3,4,5,6,7,8,9,10,11,12],
	[1,1,13,1,1,14,1,1,1,1,1,1,1,1,1,1],
	[1,1,15,1,1,16,1,1,1,1,1,1,1,1,1,1],
	[1,1,17,1,1,18,1,1,1,1,1,1,1,1,1,1],
	[1,1,19,1,1,20,1,1,1,1,1,1,1,1,1,1],
	[1,1,21,1,1,22,1,1,1,1,1,1,1,1,1,1],
	[1,1,23,1,1,24,1,1,1,1,1,1,1,1,1,1],
	[1,1,25,1,1,26,1,1,1,1,1,1,1,1,1,1],
	[1,1,27,1,1,28,1,1,1,1,1,1,1,1,1,1],
	[1,1,29,1,1,30,1,1,1,1,1,1,1,1,1,1],
	[1,1,31,1,1,32,1,1,1,1,1,1,1,1,1,1],
	[1,1,33,1,1,34,1,1,1,1,1,1,1,1,1,1]
];

my $entryTable = [
	[ 0,      0,  0],
	[ 0, 0x4000,  0],
	[ 2, 0x8000,  0],
	[ 3, 0x8000,  0],
	[ 4, 0x8000,  0],
	[ 5, 0x8000,  0],
	[ 6, 0x8000,  0],
	[ 7, 0x8000,  0],
	[ 8, 0x8000,  0],
	[ 9, 0x8000,  0],
	[10, 0x8000,  0],
	[11, 0x8000,  0],
	[12, 0x8000,  0],
	[ 2,      0,  0],
	[ 0, 0xa000,  0],
	[ 3,      0,  0],
	[ 0, 0xa000,  2],
	[ 4,      0,  0],
	[ 0, 0xa000,  4],
	[ 5,      0,  0],
	[ 0, 0xa000,  6],
	[ 6,      0,  0],
	[ 0, 0xa000,  8],
	[ 7,      0,  0],
	[ 0, 0xa000, 10],
	[ 8,      0,  0],
	[ 0, 0xa000, 12],
	[ 9,      0,  0],
	[ 0, 0xa000, 14],
	[10,      0,  0],
	[ 0, 0xa000, 16],
	[11,      0,  0],
	[ 0, 0xa000, 18],
	[12,      0,  0],
	[ 0, 0xa000, 20]
];

my $out = \calc_sequences($stateArray, $entryTable);
print Dumper $out;

sub calc_sequences
{
	my ($stateArray, $entryTable) = @_;

	# starting at state 1, recurse and build possibles.
	# the stack is empty at this point
	return recurse_states($stateArray, $entryTable, 1);
}

sub recurse_states
{
	my $stateArray = shift;
	my $entryTable = shift;
	my $state = shift;
	my $tokens = \@_;

	# for this state, check each of position from index 4 onwards (ignore 0-3)
	# then read the entry for that index - if it leads back to state 0 or 1 without
	# performing an action, then ignore it. otherwise add it to our list of possible
	# outcomes.

	for my $nClass (4..scalar(@{$stateArray->[$state]})-1){
		my $entryId = $stateArray->[$state]->[$i];
		my $entry = $entryTable->[$entryId];

		# reset rule - nothing to do here
		if (!$entry->[1] & 0xE000){
			if ($entry->[0] == 0) next;
			if ($entry->[0] == 1) next;
		}

		if ($entry->[1] & 0x8000){} # setComponent
		if ($entry->[1] & 0x4000){} # dontAdvance - hard to unroll :(
		if ($entry->[1] & 0x2000){} # performAction!
	}

}
