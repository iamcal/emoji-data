package Font::TTF::CBDT;

use strict;
use vars qw(@ISA);
require Font::TTF::Table;

@ISA = qw(Font::TTF::Table);


sub read
{
    my ($self) = shift;
    my ($fh);
    my ($i, $dat);
    my ($cblc) = $self->{' PARENT'}->{'CBLC'};
    my ($bst_array);

    $cblc->read;
    $self->SUPER::read || return $self;
    $fh = $self->{' INFILE'};

    # ebdtHeader
    $fh->read($dat, 4);	# version

    $bst_array = $cblc->{'bitmapSizeTable'};

    for ($i = 0; $i < $cblc->{'Num'}; $i++)
    {
        my ($bst) = $bst_array->[$i];
        my ($format) = $bst->{'imageFormat'};
        my ($offset) = $bst->{'imageDataOffset'};
        my ($j);
        my ($ist_array) = $cblc->{'indexSubTableArray'}[$i];
        my ($bitmap) = {};

        die "Only CBDT format 17 is implemented (this was $format)." unless  ($format == 17);

        $self->{'bitmap'}[$i] = $bitmap;

        for ($j = 0; $j < $bst->{'numberOfIndexSubTables'}; $j++) {
            my ($ista) = $ist_array->[$j];
            my ($offsetArray) = $cblc->{'indexSubTable'}[$i][$j];
            my ($p, $o0, $c);

#           if ($fh->tell != $self->{' OFFSET'} + $offset) {
#               $fh->seek($self->{' OFFSET'} + $offset, 0);
#           }

            $p = 0;
            $o0 = $offsetArray->[$p++];
            for ($c = $ista->{'firstGlyphIndex'}; $c <= $ista->{'lastGlyphIndex'}; $c++)
            {
                my ($b) = {};
                my ($o1) = $offsetArray->[$p++];
                my ($len) = $o1 - $o0 - 8;

#               if ($fh->tell != $self->{' OFFSET'} + $offset + $o0) {
#                   $fh->seek($self->{' OFFSET'} + $offset + $o0, 0);
#               }

                $fh->read($dat, 5);
                ($b->{'height'},
                 $b->{'width'},
                 $b->{'bearingX'},
                 $b->{'bearingY'},
                 $b->{'advance'})
                    = unpack("ccccc", $dat);

                $fh->read($dat, 4);
		($len) = unpack("N", $dat);

                $fh->read($dat, $len);
                $b->{'imageData'} = $dat;
                $b->{'format'} = 17; # PNG and smallMetrics

                $bitmap->{$c} = $b;
                $o0 = $o1;
            }

            $offset += $o0;
        }
    }

    $self;
}

sub get_regions
{
    my (@l) = @_;
    my (@r) = ();
    my ($e);
    my ($first);
    my ($last);

    $first = $l[0];
    $last = $first - 1;
    foreach $e (@l) {
        if ($last + 1 != $e) {	# not contiguous
            $r[++$#r] = [$first, $last];
            $first = $e;
        }

        $last = $e;
    }

    $r[++$#r] = [$first, $last];
    @r;
}

1;
