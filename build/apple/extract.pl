#!/bin/perl

use strict;
use warnings;
use Data::Dumper;
use lib '../lib';
use Font::TTF::Font;
use Font::TTF::Sbix;
use Font::TTF::Morx;

$Font::TTF::Font::tables{'sbix'} = 'Font::TTF::Sbix';
$Font::TTF::Font::tables{'morx'} = 'Font::TTF::Morx';

my $filename = "apple_color_emoji_10_10_3.ttf";

my $f = Font::TTF::Font->open($filename) || die "Unable to read $filename : $!";


#
# we ultimately want to build a mapping of glyph_id -> $filename
#

my $filenames = {};


#$f->{'cmap'}->read();
#print Dumper $f->{'cmap'}{'Tables'}[0]{'val'};
#exit;


#$f->{'morx'}->read();
#my $tbl = $f->{'morx'}->{'header'}->{'chains'}->[0]->{'subtables'}[1];

#print Dumper $tbl;

#for my $i(0..scalar(@{$tbl->{states}})-1){
#	my $h = sprintf('%04x', $tbl->{states}->[$i]->[1]);
#	print "$i : [$tbl->{states}->[$i]->[0], 0x$h, $tbl->{states}->[$i]->[2]]\n";
#}
#exit;


#
# for now, we're doing some manual
#

$filenames->{'44'} = '0023-20e3.png';
$filenames->{'45'} = '0030-20e3.png';
$filenames->{'46'} = '0031-20e3.png';
$filenames->{'47'} = '0032-20e3.png';
$filenames->{'48'} = '0033-20e3.png';
$filenames->{'49'} = '0034-20e3.png';
$filenames->{'50'} = '0035-20e3.png';
$filenames->{'51'} = '0036-20e3.png';
$filenames->{'52'} = '0037-20e3.png';
$filenames->{'53'} = '0038-20e3.png';
$filenames->{'54'} = '0039-20e3.png';


#
# use the cmap to build a simple mapping
#

if (1){
	$f->{'cmap'}->read();

	for my $uni (keys %{$f->{'cmap'}{'Tables'}[0]{'val'}}){
		my $idx = $f->{'cmap'}{'Tables'}[0]{'val'}{$uni};
		$filenames->{$idx} = sprintf('%04x', $uni).'.png';
	}

	#print Dumper $filenames;
}


#
# do skin tones last, so we're sure we're not wiping over over glyphs
#

my @skin_tones = qw(1f3fb 1f3fc 1f3fd 1f3fe 1f3ff);

my @skin_ids = (
	92,139,145,151,552,607,613,
	620,628,720,726,734,740,746,
	752,758,764,770,776,782,788,
	794,821,827,833,839,863,870,
	876,882,888,894,900,906,912,
	918,927,937,943,949,956,962,
	968,1012,1185,1261,1267,1273,
	1282,1288,1294,1300,1306,
	1347,1369,1375,1381,1396
);

for my $i (@skin_ids){
	my $base = $filenames->{$i};
	($base) = split /\./, $base;
	die "can't find base for skin at $i" unless $base;
	for my $s(1..5){
		die "already have a filename at $i+$s" if ($filenames->{$i+$s});
		$filenames->{$i+$s} = $base.'-'.$skin_tones[$s-1].'.png';
	}
}


#
# write out glyphs
#

$f->{'sbix'}->read();
$f->{'maxp'}->read();

`rm -f ../../img-apple-160-perl/*.png`;

for my $glyph_id(0..$f->{'maxp'}->{'numGlyphs'}-1){

	my $filename = $filenames->{$glyph_id};
	if ($filename){
		$filename = $glyph_id.'_'.$filename;
	}else{
		$filename = $glyph_id."_UNKNOWN.png";
	}
	my $strike = $f->{'sbix'}->read_strike(160, 0 + $glyph_id, 1);

	if ($strike->{'graphicType'} eq 'png '){
		# all good - save it
		print "ok - $filename\n";

		open(my $fh, '>', "../../img-apple-160-perl/$filename");
		print($fh  $strike->{'data'});
		close($fh);

	}elsif ($strike->{'graphicType'} eq 'zero-length'){

		print "no glyph for $filename\n";

	}else{
		# something we don't expect
		print "unexpected glyph type ($strike->{'graphicType'}) for index $glyph_id (filename $filename)\n";
	}
}

#$f->{'sbix'}->read();
#print Dumper $f->{'sbix'};

#my $strike = $f->{'sbix'}->read_strike(160, 505);
#print Dumper $f->{'sbix'};
#print Dumper $strike;

#$f->{'maxp'}->read();
#print Dumper $f->{'maxp'};
