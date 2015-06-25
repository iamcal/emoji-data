package Font::TTF::Morx;

use strict;
use vars qw(@ISA);
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
    }

    $self->{'header'} = $header;

    $self;
}


1;
