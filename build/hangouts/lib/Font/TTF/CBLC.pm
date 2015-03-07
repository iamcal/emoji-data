package Font::TTF::CBLC;

use strict;
use vars qw(@ISA);
require Font::TTF::Table;

@ISA = qw(Font::TTF::Table);

sub read
{
    my ($self) = @_;

    $self->SUPER::read or return $self;

    my ($fh) = $self->{' INFILE'};
    my ($i, $dat);
    my ($indexSubTableArrayOffset,
        $indexTablesSize,
        $numberOfIndexSubTables,
        $colorRef);
    my ($startGlyphIndex,
        $endGlyphIndex,
        $ppemX, $ppemY,
        $bitDepth, $flags);
    my (@hori, @vert);
    my ($bst, $ista, $ist);
    my ($j);


    # eblcHeader
    $fh->read($dat, 4);
    $self->{'version'} = unpack("N",$dat);

    $fh->read($dat, 4);
    $self->{'Num'} = unpack("N",$dat);

    # bitmapSizeTable
    for ($i = 0; $i < $self->{'Num'}; $i++) {
        $fh->read($dat, 16);
        ($indexSubTableArrayOffset, $indexTablesSize,
         $numberOfIndexSubTables, $colorRef) = unpack("NNNN", $dat);
        $fh->read($dat, 12); @hori = unpack("cccccccccccc", $dat);
        $fh->read($dat, 12); @vert = unpack("cccccccccccc", $dat);

        $fh->read($dat, 8);
        ($startGlyphIndex, $endGlyphIndex,
         $ppemX, $ppemY, $bitDepth, $flags) = unpack("nnCCCC", $dat);

        $self->{'bitmapSizeTable'}[$i] = {
            'indexSubTableArrayOffset' => $indexSubTableArrayOffset,
            'indexTablesSize' => $indexTablesSize,
            'numberOfIndexSubTables' => $numberOfIndexSubTables,
            'colorRef' => $colorRef,
            'hori' => [@hori],
            'vert' => [@vert],
            'startGlyphIndex' => $startGlyphIndex,
            'endGlyphIndex' => $endGlyphIndex,
            'ppemX' => $ppemX,
            'ppemY' => $ppemY,
            'bitDepth' => $bitDepth,
            'flags' => $flags
            };
    }

    for ($i = 0; $i < $self->{'Num'}; $i++) {
        my ($count, $x);

        $bst = $self->{'bitmapSizeTable'}[$i];

        for ($j = 0; $j < $bst->{'numberOfIndexSubTables'}; $j++) {
            $ista = {};

            # indexSubTableArray
            $self->{'indexSubTableArray'}[$i][$j] = $ista;
            $fh->read($dat, 8);
            ($ista->{'firstGlyphIndex'},
             $ista->{'lastGlyphIndex'},
             $ista->{'additionalOffsetToIndexSubtable'})
                = unpack("nnN", $dat);
        }

        # indexSubTable
        # indexSubHeader
        $fh->read($dat, 8);
        ($bst->{'indexFormat'}, 
         $bst->{'imageFormat'}, 
         $bst->{'imageDataOffset'}) = unpack("nnN", $dat);

        die "Only indexFormat == 1 is supported" unless ($bst->{'indexFormat'} == 1);

        for ($j = 0; $j < $bst->{'numberOfIndexSubTables'}; $j++) {
            $ista = $self->{'indexSubTableArray'}[$i][$j];
            $count = $ista->{'lastGlyphIndex'} - $ista->{'firstGlyphIndex'} + 1 + 1;
            $fh->seek($self->{' OFFSET'} + $bst->{'indexSubTableArrayOffset'}
                      + $ista->{'additionalOffsetToIndexSubtable'} + 8, 0);

#           $count += 2 if $j < $bst->{'numberOfIndexSubTables'} - 1;

            $fh->read($dat, 4*$count);

            $self->{'indexSubTable'}[$i][$j] = [unpack("N*", $dat)];
        }
    }

    $self;
}

1;
