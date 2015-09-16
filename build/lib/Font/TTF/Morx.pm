package Font::TTF::Morx;

use strict;
use vars qw(@ISA);
use Font::TTF::AATutils;
use Data::Dumper;
require Font::TTF::Table;

@ISA = qw(Font::TTF::Table);


sub read
{
    my ($self) = shift;
    my ($fh, $dat);

    $self->SUPER::read || return $self;
    $fh = $self->{' INFILE'};

    my $header = {};

    $fh->read($dat, 8);	# header
    ($header->{'version'},
     $header->{'flags'},
     $header->{'nChains'}) = unpack('nnN', $dat);

    $header->{'chains'} = [];
    for my $i(0..$header->{'nChains'}-1){
        my $chain = {};
        $fh->read($dat, 16);
        ($chain->{'defaultFlags'},
         $chain->{'chainLength'},
         $chain->{'nFeatureEntries'},
         $chain->{'nSubtables'}) = unpack('NNNN', $dat);

        push @{$header->{'chains'}}, $chain;

        $chain->{'features'} = [];
        for my $j(1..$chain->{'nFeatureEntries'}){
            my $feature = {};
            $fh->read($dat, 12);
            ($feature->{'featureType'},
             $feature->{'featureSetting'},
             $feature->{'enableFlags'},
             $feature->{'disableFlags'}) = unpack('nnNN', $dat);
            push @{$chain->{'features'}}, $feature;
        }

        $chain->{'subtables'} = [];
        for my $j(1..$chain->{'nSubtables'}){
            my $subtable = {};
            $fh->read($dat, 12);
            ($subtable->{'length'},
             $subtable->{'coverage'},
             $subtable->{'subFeatureFlags'}) = unpack('NNN', $dat);
            $subtable->{'type'} = 0xFF & $subtable->{'coverage'};

            my $cursor = $fh->tell() + $subtable->{'length'} - 12;

            if ($subtable->{'type'} == 2){ $self->read_ligatures($subtable, $fh, $cursor); }

            push @{$chain->{'subtables'}}, $subtable;
            $fh->seek($cursor, 0);
        }
    }

    $self->{'header'} = $header;

    $self;
}

sub read_ligatures
{
	my ($self, $subtable, $fh, $endOffset) = @_;

	my $dat;
	my $start = $fh->tell();

	$fh->read($dat, 7 * 4);

	my $header = {};
	($header->{'nClasses'},
	 $header->{'classTableOffset'},
	 $header->{'stateArrayOffset'},
	 $header->{'entryTableOffset'},
	 $header->{'ligActionOffset'},
	 $header->{'componentOffset'},
	 $header->{'ligatureOffset'}) = unpack('NNNNNNN', $dat);
	$subtable->{'header'} = $header;

	# we'll read the 6 tables, which we can later use to walk
	# and build the actual map
	my $tables = {};

	# read class table
	my $len = $header->{'stateArrayOffset'} - $header->{'classTableOffset'};
	$fh->seek($start + $header->{'classTableOffset'}, 0);
	my ($classFormat, $classLookup) = Font::TTF::AATutils::AAT_read_lookup($fh, 2, $len, 0);
	$tables->{'classTable'} = $classLookup;

	# read state array
	my $len = $header->{'entryTableOffset'} - $header->{'stateArrayOffset'};
	$fh->seek($start + $header->{'stateArrayOffset'}, 0);
	my $nStates = $len / (2 * $header->{'nClasses'});
	$tables->{'stateArray'} = [];
	for my $i(1..$nStates){
		$fh->read($dat, $header->{'nClasses'} * 2);
		push @{$tables->{'stateArray'}}, [unpack('n*', $dat)];
	}

	# read entry table
	my $len = $header->{'ligActionOffset'} - $header->{'entryTableOffset'};
	$fh->seek($start + $header->{'entryTableOffset'}, 0);
	my $entries = $len / 6;
	$tables->{'entryTable'} = [];
	for my $i(1..$entries){
		$fh->read($dat, 6);
		push @{$tables->{'entryTable'}}, [unpack('nnn', $dat)];
	}

	# read lig action
	my $len = $header->{'componentOffset'} - $header->{'ligActionOffset'};
	$fh->seek($start + $header->{'ligActionOffset'}, 0);
	$fh->read($dat, $len);
	$tables->{'ligActions'} = [unpack('N*', $dat)];

	# read component
	my $len = $header->{'ligatureOffset'} - $header->{'componentOffset'};
	$fh->seek($start + $header->{'ligatureOffset'}, 0);
	$fh->read($dat, $len);
	$tables->{'components'} = [unpack('n*', $dat)];

	# read ligature
	my $len = $endOffset - $header->{'ligatureOffset'};
	$fh->seek($start + $header->{'ligatureOffset'}, 0);
	$fh->read($dat, $len);
	$tables->{'ligatures'} = [unpack('n*', $dat)];

	$subtable->{'tables'} = $tables;	
}

sub resolve_ligature {
	my ($self, $cps) = @_;

	for my $chain_id(0..scalar(@{$self->{'header'}->{'chains'}})-1){
		#print "scanning chain ${chain_id}...\n";

		for my $subtable_id(0..scalar(@{$self->{'header'}->{'chains'}->[$chain_id]->{'subtables'}})-1){

			#print "  scanning subtable ${subtable_id}...\n";

			my $ret = $self->resolve_ligature_table($cps, $self->{'header'}->{'chains'}->[$chain_id]->{'subtables'}->[$subtable_id]);
			return $ret if $ret;
		}
	}

	return 0;
}

sub resolve_ligature_table {
	my ($self, $cps, $table) = @_;

	#
	# only resolve from ligature subtables, not swashes, etc.
	#

	if ($table->{'type'} != 2){
		#print "not a lig table\n";
		return 0;
	}

	my $num = scalar(%{$table->{'tables'}->{'classTable'}});

	print "classTable size: ${num}\n";

	return 0;
}


1;
