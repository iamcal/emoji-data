#!/bin/perl

use strict;
use warnings;
use Data::Dumper;
use lib '../lib';
use Font::TTF::Font;
use Font::TTF::Sbix;
use Font::TTF::Morx;
use Font::TTF::TTC;

$Font::TTF::Font::tables{'sbix'} = 'Font::TTF::Sbix';
$Font::TTF::Font::tables{'morx'} = 'Font::TTF::Morx';

my $filename = "apple_color_emoji_10_14_1.ttc";

my $f = Font::TTF::TTC->openCollection($filename) || die "Unable to read $filename : $!";
$f->readCollection(0);


#
# we ultimately want to build a mapping of glyph_id -> $filename
#

my $filenames = {};
my $duplicates = [];


#
# first we'll process ligatures we know about
#

$f->{'morx'}->read();

my @ligatures;
my $path_maps = {};

sub hex_to_cps {
	my @chars = split(/-/, shift);
	my $cps = [];

	for my $str(@chars){
		push @{$cps}, hex($str);
	}

	return $cps;
}

sub cps_to_path {
	my $cps = shift;
	my @components;
	for my $cp(@{$cps}){
		push @components, sprintf('%04x', $cp);
	}
	return join('-', @components).'.png';
}


#
# we extract the ligatures from emoji.json, then add them to our list
#

my $lines = `php find_ligatures.php`;
my @lines = split /\n/, $lines;

for my $line(@lines){
	# lines are of the format:
	# 1F3C2-1F3FB  :snowboarder: (tone 1F3FB)

	my ($hex, $junk) = split(/\s+/, $line);
	my $cps = &hex_to_cps($hex);

	push @ligatures, $cps;
}


#
# we find a list of alternative ligatures which we might need to use
#

$lines = `php find_non_qualified.php`;
@lines = split /\n/, $lines;

for my $line(@lines){
	# lines are of the format:
	# ABCD-FE0F  ABCD

	my ($hex_fq, $hex_nq) = split(/\s+/, $line);
	my $cps = &hex_to_cps($hex_nq);

	push @ligatures, $cps;

	$path_maps->{&cps_to_path($cps)} = &cps_to_path(&hex_to_cps($hex_fq));
}

# extended/ambiguous ligatures are still broken, so these are all manual.
# when upgrading the TTF, blank these out before running this - values will have changed!

#$filenames->{'994'} = '1f468-200d-1f468-200d-1f466-200d-1f466.png'; # mmbb
#$filenames->{'996'} = '1f468-200d-1f468-200d-1f467-200d-1f466.png'; # mmgb
#$filenames->{'997'} = '1f468-200d-1f468-200d-1f467-200d-1f467.png'; # mmgg

#$filenames->{'998'} = '1f468-200d-1f469-200d-1f466-200d-1f466.png'; # mwbb
#$filenames->{'1000'} = '1f468-200d-1f469-200d-1f467-200d-1f466.png'; # mwgb
#$filenames->{'1001'} = '1f468-200d-1f469-200d-1f467-200d-1f467.png'; # mwgg

#$filenames->{'1003'} = '1f469-200d-1f469-200d-1f466-200d-1f466.png'; # wwbb
#$filenames->{'1005'} = '1f469-200d-1f469-200d-1f467-200d-1f466.png'; # wwgb
#$filenames->{'1006'} = '1f469-200d-1f469-200d-1f467-200d-1f467.png'; # wwgg



#
# ok, time to process those ligatures.
# we wont find all of them, but that's fine
#

for my $lig(@ligatures){
	my $glyph = $f->{'morx'}->resolve_ligature($lig);
	if ($glyph){
		my $key = ''.$glyph;
		my $path = &cps_to_path($lig);

		if ($path_maps->{$path}){
			$path = $path_maps->{$path};
		}

		if ($filenames->{$key}){
			push @{$duplicates}, [$key, $path];
		}else{
			$filenames->{$key} = $path;
		}
	}
}


#
# use the cmap to build a simple mapping for non-ligatures
#

my $simple_map = {};

if (1){
	$f->{'cmap'}->read();

	for my $uni (keys %{$f->{'cmap'}{'Tables'}[0]{'val'}}){
		my $idx = $f->{'cmap'}{'Tables'}[0]{'val'}{$uni};

		my $path = sprintf('%04x', $uni).'.png';

		if ($path_maps->{$path}){
			$path = $path_maps->{$path};
		}

		if ($filenames->{$idx}){
			push @{$duplicates}, [$idx, $path];
		}else{
			$filenames->{$idx} = $path;
		}

		$simple_map->{$idx} = $uni;
	}

	#print Dumper $filenames;
}


#
# special mode - find likely skin-tone codepoints that were not processed
#

if (0){
	for my $glyph_id(0..$f->{'maxp'}->{'numGlyphs'}-1){

		if ($filenames->{$glyph_id}
			&& !$filenames->{$glyph_id+1}
			&& !$filenames->{$glyph_id+2}
			&& !$filenames->{$glyph_id+3}
			&& !$filenames->{$glyph_id+4}
			&& !$filenames->{$glyph_id+5}){

			my $up = sprintf('%04X;', $simple_map->{$glyph_id});
			my $line = `grep -E "^$up" ../unicode/UnicodeData.txt`;
			my @bits = split /;/, $line;

			printf(" 0x%x, # %s\n", $simple_map->{$glyph_id}, lc $bits[1]);
		}
	}
	exit;
}


#
# write out glyphs
#

$f->{'sbix'}->read();
$f->{'maxp'}->read();

`rm -f ../../img-apple-160/*.png`;

for my $glyph_id(0..$f->{'maxp'}->{'numGlyphs'}-1){

	my $filename = $filenames->{$glyph_id};
	unless ($filename){
		$filename = $glyph_id."_UNKNOWN.png";
	}

	&store_image($glyph_id, $filename);
}

for my $pair(@{$duplicates}){

	&store_image($pair->[0], $pair->[1]);
}


sub store_image {
	my ($glyph_id, $filename) = @_;

	my $strike = $f->{'sbix'}->read_strike(160, 0 + $glyph_id, 1);

	if ($strike->{'graphicType'} eq 'png '){
		# all good - save it
		print "ok - $filename\n";

		open(my $fh, '>', "../../img-apple-160/$filename");
		print($fh  $strike->{'data'});
		close($fh);

	}elsif ($strike->{'graphicType'} eq 'zero-length'){

		print "no glyph for $filename\n";

	}else{
		# something we don't expect
		print "unexpected glyph type ($strike->{'graphicType'}) for index $glyph_id (filename $filename)\n";
	}
}
