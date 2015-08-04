package Font::TTF::COLR;

use strict;
use vars qw(@ISA);
use Data::Dumper;
require Font::TTF::Table;

@ISA = qw(Font::TTF::Table);

# https://www.microsoft.com/typography/otspec/colr.htm
# BYTES/CHAR -> c
# uint16/USHORT -> n
# uint32/ULONG -> N

sub read
{
    my ($self) = shift;
    my ($cpal) = $self->{' PARENT'}->{'CPAL'};
    my ($fh, $dat);

    $cpal->read;
    $self->SUPER::read || return $self;
    $fh = $self->{' INFILE'};

    # read header
    $fh->read($dat, 14);
    $self->{'header'} = {};
    ($self->{'header'}->{'version'},
     $self->{'header'}->{'numBaseGlyphRecords'},
     $self->{'header'}->{'offsetBaseGlyphRecord'},
     $self->{'header'}->{'offsetLayerRecord'},
     $self->{'header'}->{'numLayerRecords'}) = unpack('nnNNn', $dat);

    # read base glyphs
    my $base_glyphs = [];
    $fh->seek($self->{' OFFSET'} + $self->{'header'}->{'offsetBaseGlyphRecord'}, 0);
    for my $idx(0..$self->{'header'}->{'numBaseGlyphRecords'}-1){
        $fh->read($dat, 6);
        my @bg = unpack('nnn', $dat);
        $base_glyphs->[$idx] = \@bg;
    }

    # read layer records
    my $layer_records = [];
    $fh->seek($self->{' OFFSET'} + $self->{'header'}->{'offsetLayerRecord'}, 0);
    for my $idx(0..$self->{'header'}->{'numLayerRecords'}-1){
        $fh->read($dat, 4);
        my @lr = unpack('nn', $dat);
        $layer_records->[$idx] = \@lr;
    }

    # build glyph list
    $self->{'glyphs'} = {};
    for my $bg(@{$base_glyphs}){
        my @layers;
        for my $idx(0..$bg->[2]-1){
            push @layers, $layer_records->[$idx + $bg->[1]];
        }
        $self->{'glyphs'}->{$bg->[0]} = \@layers;
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

1;
