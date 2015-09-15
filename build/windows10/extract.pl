#!/bin/perl

use strict;
use warnings;
use Data::Dumper;
use lib '../lib';
use Font::TTF::Font;
use Font::TTF::COLR;
use Font::TTF::CPAL;

# notes on TTF->SVG:
# https://github.com/donbright/font_to_svg/blob/master/README.md

$Font::TTF::Font::tables{'COLR'} = 'Font::TTF::COLR';
$Font::TTF::Font::tables{'CPAL'} = 'Font::TTF::CPAL';

my $filename = "seguiemj.ttf";

my $f = Font::TTF::Font->open($filename) || die "Unable to read $filename";


#
# use the cmap to build a simple mapping of glyph ID -> filename
#

$f->{'cmap'}->read();

my $filenames = {};
for my $uni (keys %{$f->{'cmap'}{'Tables'}[0]{'val'}}){
	my $idx = $f->{'cmap'}{'Tables'}[0]{'val'}{$uni};
	$filenames->{$idx} = sprintf('%04x', $uni);
}


#
# read the GSUB table to build a list of ligatures
#

$f->{'GSUB'}->read();
my $groups = $f->{'GSUB'}{'LOOKUP'}[0]{'SUB'}[0]{'RULES'};
my $cover = $f->{'GSUB'}{'LOOKUP'}[0]{'SUB'}[0]{'COVERAGE'};
my %map;

for my $glyph(keys %{$cover->{'val'}}){
	my $idx = $cover->{'val'}{$glyph};
	for my $row (@{$groups->[$idx]}){

		my $uni_a = $filenames->{$glyph};
		my $uni_b = $filenames->{$row->{'MATCH'}[0]};
		my $idx2 = $row->{'ACTION'}[0];

		$filenames->{$idx2} = "${uni_a}-${uni_b}";
	}
}

print Dumper $filenames;
exit;

#
# now read the data chunks for glyphs:
# loca - mapping glyph IDs to `glyf` data
# glyf - outlines
# CPAL - color palettes
# COLR - composite glyphs
#

$f->{'loca'}->read();
$f->{'glyf'}->read();
$f->{'COLR'}->read();



#
# turn a list of contour points into an SVG path
#

sub build_path {
    my ($points) = @_;

    my $first = shift @{$points};
    my $path = "M$first->[0],$first->[1] ";

    while (scalar @{$points}){
        my $next = shift @{$points};
        if ($next->[2]){
          # easy case - on curve
          $path .= "L$next->[0],$next->[1] ";
        }else{
          # complex case - off curve.
          # start a quadratic curve. if the next point is on curve, easy
          # if the next point is off curve, create an implicit on-curve point between them.
          $path .= "Q$next->[0],$next->[1] ";
          my $after = scalar(@{$points}) ? shift @{$points} : $first;
          if ($after->[2]){
            $path .= "$after->[0],$after->[1] ";
          }else{
            my $implicit_on_x = ($next->[0] + $after->[0]) / 2;
            my $implicit_on_y = ($next->[1] + $after->[1]) / 2;
            $path .= "$implicit_on_x,$implicit_on_y ";
            unshift @{$points}, $after;
          }
        }
    }

    $path .= 'Z';
    return $path;
}


#
# draw a compound color glyph
#

sub draw_colr {
    my ($list) = @_;

    # load all of the component glyphs
    my @components;
    for my $pair(@{$list}){
        my $ch = $f->{'loca'}->{'glyphs'}->[$pair->[0]];
        my $col = $f->{'CPAL'}->{'palettes'}->[0]->[$pair->[1]];
        $ch->read();
        $ch->read_dat();
        $ch->{'color'} = $col;
        push @components, $ch;
    }

    # find extents
    my $min_x = $components[0]->{'x'}->[0];
    my $max_x = $components[0]->{'x'}->[0];

    my $min_y = $components[0]->{'y'}->[0];
    my $max_y = $components[0]->{'y'}->[0];

    for my $ch(@components){
        for my $i(0..$ch->{'numPoints'}-1){

            if (!($ch->{'flags'}->[$i] & 1)){ next; }

            if ($ch->{'x'}->[$i] < $min_x){ $min_x = $ch->{'x'}->[$i]; }
            if ($ch->{'x'}->[$i] > $max_x){ $max_x = $ch->{'x'}->[$i]; }

            if ($ch->{'y'}->[$i] < $min_y){ $min_y = $ch->{'y'}->[$i]; }
            if ($ch->{'y'}->[$i] > $max_y){ $max_y = $ch->{'y'}->[$i]; }
        }
    }

    my $size_x = $max_x - $min_x;
    my $size_y = $max_y - $min_y;

    my $svg = "<svg height=\"$size_y\" width=\"$size_x\">\n\n";

    # build paths
    for my $ch(@components){
        my @paths;
        for my $c(0..$ch->{'numberOfContours'}-1){
            my $start = $c ? $ch->{'endPoints'}->[$c-1]+1 : 0;
            my $end = $ch->{'endPoints'}->[$c];
            my @contour;
            for my $p($start..$end){
                my $x = $ch->{'x'}->[$p] - $min_x;
                my $y = $size_y - ($ch->{'y'}->[$p] - $min_y);
                my $f = $ch->{'flags'}->[$p] & 1;
                push @contour, [$x, $y, $f];
            }
            push @paths, build_path(\@contour);
        }
        my $r = $ch->{'color'}->[0];
        my $g = $ch->{'color'}->[1];
        my $b = $ch->{'color'}->[2];
        my $a = $ch->{'color'}->[3] / 255;

        $svg .= "<g fill-rule=\"nonzero\" fill=\"rgb($r,$g,$b)\" fill-opacity=\"$a\">\n";
        $svg .= "  <path d=\"".(join "\n", @paths)."\" />\n";
        $svg .= "</g>\n\n";
    }

    $svg .= "</svg>\n";

    return $svg;
}

sub export_colr
{
    my ($idx, $filename) = @_;

    my $svg = draw_colr($f->{'COLR'}->{'glyphs'}->{$idx});

    open(my $fh, '>', "../../img-windows10-svg/$filename");
    print($fh "<html>\n<head>\n</head>\n<body>\n");
    print($fh $svg);
    print($fh "</body>\n</html>\n");
    close($fh);
}



for my $key (keys %{$f->{'COLR'}->{'glyphs'}}){

    my $name = $filenames->{$key} || "unknown_${key}";

    export_colr($key, $name.'.htm');

    print "$name.htm\n";
}
exit;




#
# draw a single outline glyph
#

my $ch = $f->{'loca'}->{'glyphs'}->[1998];
$ch->read();
$ch->read_dat();

my $min_x = $ch->{'x'}->[0];
my $max_x = $ch->{'x'}->[0];

my $min_y = $ch->{'y'}->[0];
my $max_y = $ch->{'y'}->[0];

for my $i(0..$ch->{'numPoints'}-1){

    if (!($ch->{'flags'}->[$i] & 1)){ next; }

    if ($ch->{'x'}->[$i] < $min_x){ $min_x = $ch->{'x'}->[$i]; }
    if ($ch->{'x'}->[$i] > $max_x){ $max_x = $ch->{'x'}->[$i]; }

    if ($ch->{'y'}->[$i] < $min_y){  $min_y = $ch->{'y'}->[$i]; }
    if ($ch->{'y'}->[$i] > $max_y){ $max_y = $ch->{'y'}->[$i]; }
}


#delete $ch->{' PARENT'};
#print Dumper $ch;
#exit;

my @paths;
for my $c(0..$ch->{'numberOfContours'}-1){
    my $start = $c ? $ch->{'endPoints'}->[$c-1]+1 : 0;
    my $end = $ch->{'endPoints'}->[$c];
    my @contour;
    for my $p($start..$end){
        my $x = $ch->{'x'}->[$p] - $min_x;
        my $y = ($max_y - $ch->{'y'}->[$p]) - $min_y;
        my $f = $ch->{'flags'}->[$p] & 1;
        push @contour, [$x, $y, $f];
    }
    push @paths, build_path(\@contour);
}

print "<g fill-rule=\"nonzero\" fill=\"red\" stroke=\"black\" stroke-width=\"3\">\n";
print "  <path d=\"".(join "\n", @paths)."\" />\n";
print "</g>\n";




#print Dumper \@contours;

#delete $ch->{' PARENT'};
#print Dumper $ch;
#print Dumper $filenames;


exit;
__END__
$f->{'CBLC'}->read();
$f->{'CBDT'}->read();

my @keys = keys(%{$f->{'CBDT'}{'bitmap'}[0]});

foreach my $key (@keys){

	my $name = $filenames->{$key} || "unknown_${key}";

	open(my $fh, '>', "../../img-google-136/${name}.png");
	print($fh $f->{'CBDT'}{'bitmap'}[0]{$key}{imageData});
	close($fh);

	print "key $key, $name.png \n";
}
