package Font::TTF::Sbix;

use strict;
use vars qw(@ISA);
require Font::TTF::Table;

@ISA = qw(Font::TTF::Table);


sub read
{
    my ($self) = shift;
    my ($fh, $dat);

    my ($maxp) = $self->{' PARENT'}->{'maxp'};
    $maxp->read;

    $self->SUPER::read || return $self;
    $fh = $self->{' INFILE'};

    my $header = {};
    $header->{'numGlyphs'} = $maxp->{'numGlyphs'};

    $fh->read($dat, 8);	# header
    ($header->{'version'},
     $header->{'flags'},
     $header->{'numStrikes'}) = unpack('nnN', $dat);

    $header->{'strikes'} = [];
    for my $i(1..$header->{'numStrikes'}){
      $fh->read($dat, 4);
      my ($temp) = unpack('N', $dat);
      push @{$header->{'strikes'}}, { 'offset' => $temp };
    }

    for my $i(0..$header->{'numStrikes'}-1){
      $fh->seek($self->{' OFFSET'} + $header->{'strikes'}->[$i]->{'offset'}, 0);
      $fh->read($dat, 8);
      ($header->{'strikes'}->[$i]->{'ppem'},
       $header->{'strikes'}->[$i]->{'resolution'}) = unpack('nn', $dat);
      #.... read strike here
    }


    $self->{'header'} = $header;

    $self;
}

sub read_strike
{
    my ($self) = shift;
    my ($ppem, $index, $read_data) = @_;
    my ($dat);

    # find the strike offset first
    my $strike_offset = 0;
    for my $i(0..$self->{'header'}->{'numStrikes'}-1){
        if ($self->{'header'}->{'strikes'}->[$i]->{'ppem'} == $ppem){
            $strike_offset = $self->{'header'}->{'strikes'}->[$i]->{'offset'};
            last;
        }
    }
    if (!$strike_offset){ return { 'graphicType' => 'no-strike' }; }

    # now find the data offset
    my $fh = $self->{' INFILE'};
    $fh->seek($self->{' OFFSET'} + $strike_offset + 4 + (4 * $index), 0);
    $fh->read($dat, 8);
    my ($glyph_offset, $next_offset) = unpack('NN', $dat);
    my $len = $next_offset - $glyph_offset;
    if (!$len){ return { 'graphicType' => 'zero-length' }; }

    my $glyph = {};
    $glyph->{'len'} = $len - 8;

    # now read the data
    $fh->seek($self->{' OFFSET'} + $strike_offset + $glyph_offset, 0);
    $fh->read($dat, 8);
    ($glyph->{'originOffsetX'},
     $glyph->{'originOffsetY'},
     $glyph->{'graphicType'}) = unpack('n!n!a4', $dat);

    if ($read_data){
        $glyph->{'data'} = undef;
        $fh->read($glyph->{'data'}, $len - 8);
    }

    return $glyph;
}

1;
