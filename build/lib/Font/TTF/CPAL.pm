package Font::TTF::CPAL;

use strict;
use vars qw(@ISA);
use Data::Dumper;
require Font::TTF::Table;

@ISA = qw(Font::TTF::Table);

# https://www.microsoft.com/typography/otspec/cpal.htm
# BYTES/CHAR -> c
# uint16/USHORT -> n
# uint32/ULONG -> N

sub read
{
    my ($self) = shift;
    my ($fh, $dat);

    $self->SUPER::read || return $self;
    $fh = $self->{' INFILE'};

    # Palette Table Header
    $self->{'header'} = {};
    $fh->read($dat, 12);

    ($self->{'header'}->{'version'},
     $self->{'header'}->{'numPalettesEntries'},
     $self->{'header'}->{'numPalette'},
     $self->{'header'}->{'numColorRecords'},
     $self->{'header'}->{'offestFirstColorRecord'}) = unpack('nnnnN', $dat);

    if ($self->{'header'}->{'version'} != 0){ die("only version 0 CPAL tables are supported"); }

    # get index offsets
    $fh->read($dat, 2 * $self->{'header'}->{'numPalette'});
    my @indicies = unpack('n' x $self->{'header'}->{'numPalette'}, $dat);

    # read the palettes
    $self->{'palettes'} = [];
    for my $idx(0..$self->{'header'}->{'numPalette'}-1){
        # start reading at table offset + ($indicies[$idx]*4)
        $fh->seek($self->{' OFFSET'} + $self->{'header'}->{'offestFirstColorRecord'} + ($indicies[$idx]*4), 0);
        $self->{'palettes'}->[$idx] = [];
        for my $c(0..$self->{'header'}->{'numPalettesEntries'}){
            $fh->read($dat, 4);
            my @cr = unpack('CCCC', $dat);
            $self->{'palettes'}->[$idx]->[$c] = \@cr;
        }
    }

    $self;
}

sub dump
{
    my ($self) = shift;
    my $tmp = $self->{' PARENT'};
    delete $self->{' PARENT'};
    print Dumper $self;
    $self->{' PARENT'} = $tmp;
}
