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

my $filename = "apple_color_emoji_10_11_1.ttf";

my $f = Font::TTF::Font->open($filename) || die "Unable to read $filename : $!";


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


#
# we have some that are just static
#

push @ligatures, [0x0023, 0x20e3]; # keycap #
push @ligatures, [0x0030, 0x20e3]; # keycap 0
push @ligatures, [0x0031, 0x20e3]; # keycap 1
push @ligatures, [0x0032, 0x20e3]; # keycap 2
push @ligatures, [0x0033, 0x20e3]; # keycap 3
push @ligatures, [0x0034, 0x20e3]; # keycap 4
push @ligatures, [0x0035, 0x20e3]; # keycap 5
push @ligatures, [0x0036, 0x20e3]; # keycap 6
push @ligatures, [0x0037, 0x20e3]; # keycap 7
push @ligatures, [0x0038, 0x20e3]; # keycap 8
push @ligatures, [0x0039, 0x20e3]; # keycap 9
push @ligatures, [0x002a, 0x20e3]; # keycap *

# families
push @ligatures, [0x1f468, 0x200d, 0x1f468, 0x200d, 0x1f466]; # mmb
push @ligatures, [0x1f468, 0x200d, 0x1f468, 0x200d, 0x1f467]; # mmg
#push @ligatures, [0x1f468, 0x200d, 0x1f468, 0x200d, 0x1f466, 0x200d, 0x1f466]; # mmbb
#push @ligatures, [0x1f468, 0x200d, 0x1f468, 0x200d, 0x1f467, 0x200d, 0x1f466]; # mmgb
#push @ligatures, [0x1f468, 0x200d, 0x1f468, 0x200d, 0x1f467, 0x200d, 0x1f467]; # mmgg

push @ligatures, [0x1f468, 0x200d, 0x1f469, 0x200d, 0x1f466]; # mwb
push @ligatures, [0x1f468, 0x200d, 0x1f469, 0x200d, 0x1f467]; # mwg
#push @ligatures, [0x1f468, 0x200d, 0x1f469, 0x200d, 0x1f466, 0x200d, 0x1f466]; # mwbb
#push @ligatures, [0x1f468, 0x200d, 0x1f469, 0x200d, 0x1f467, 0x200d, 0x1f466]; # mwgb
#push @ligatures, [0x1f468, 0x200d, 0x1f469, 0x200d, 0x1f467, 0x200d, 0x1f467]; # mwgg

push @ligatures, [0x1f469, 0x200d, 0x1f469, 0x200d, 0x1f466]; # wwb
push @ligatures, [0x1f469, 0x200d, 0x1f469, 0x200d, 0x1f467]; # wwg
#push @ligatures, [0x1f469, 0x200d, 0x1f469, 0x200d, 0x1f466, 0x200d, 0x1f466]; # wwbb
#push @ligatures, [0x1f469, 0x200d, 0x1f469, 0x200d, 0x1f467, 0x200d, 0x1f466]; # wwgb
#push @ligatures, [0x1f469, 0x200d, 0x1f469, 0x200d, 0x1f467, 0x200d, 0x1f467]; # wwgg

# couples
push @ligatures, [0x1f468, 0x200d, 0x2764, 0xfe0f, 0x200d, 0x1f48b, 0x200d, 0x1f468]; # m-kiss-m
push @ligatures, [0x1f469, 0x200d, 0x2764, 0xfe0f, 0x200d, 0x1f48b, 0x200d, 0x1f469]; # w-kiss-w
push @ligatures, [0x1f468, 0x200d, 0x2764, 0xfe0f, 0x200d, 0x1f468]; # m-heart-m
push @ligatures, [0x1f469, 0x200d, 0x2764, 0xfe0f, 0x200d, 0x1f469]; # w-heart-w


# extended/ambiguous ligatures are still broken, so these are all manual.
# when upgrading the TTF, blank these out before running this - values will have changed!

$filenames->{'999'} = '1f468-200d-1f468-200d-1f466-200d-1f466.png'; # mmbb
$filenames->{'1001'} = '1f468-200d-1f468-200d-1f467-200d-1f466.png'; # mmgb
$filenames->{'1002'} = '1f468-200d-1f468-200d-1f467-200d-1f467.png'; # mmgg

$filenames->{'1003'} = '1f468-200d-1f469-200d-1f466-200d-1f466.png'; # mwbb
$filenames->{'1005'} = '1f468-200d-1f469-200d-1f467-200d-1f466.png'; # mwgb
$filenames->{'1006'} = '1f468-200d-1f469-200d-1f467-200d-1f467.png'; # mwgg

$filenames->{'1008'} = '1f469-200d-1f469-200d-1f466-200d-1f466.png'; # wwbb
$filenames->{'1010'} = '1f469-200d-1f469-200d-1f467-200d-1f466.png'; # wwgb
$filenames->{'1011'} = '1f469-200d-1f469-200d-1f467-200d-1f467.png'; # wwgg


#
# for skin tones, we'll take a list of known emoji with skin variations,
# and pair each with the 5 skin tones
#

my @skin_tones = (0x1f3fb, 0x1f3fc, 0x1f3fd, 0x1f3fe, 0x1f3ff);
my @skin_ids = (
	0x261d, # white up pointing index
	0x270a, # raised fist
	0x270b, # raised hand
	0x270c, # victory hand
	0x1f385, # father christmas
	0x1f3c3, # runner
	0x1f3c4, # surfer
	0x1f3c7, # horse racing
	0x1f3ca, # swimmer
	0x1f442, # ear
	0x1f443, # nose
	0x1f446, # white up pointing backhand index
	0x1f447, # white down pointing backhand index
	0x1f448, # white left pointing backhand index
	0x1f449, # white right pointing backhand index
	0x1f44a, # fisted hand sign
	0x1f44b, # waving hand sign
	0x1f44c, # ok hand sign
	0x1f44d, # thumbs up sign
	0x1f44e, # thumbs down sign
	0x1f44f, # clapping hands sign
	0x1f450, # open hands sign
	0x1f466, # boy
	0x1f467, # girl
	0x1f468, # man
	0x1f469, # woman
	0x1f46e, # police officer
	0x1f470, # bride with veil
	0x1f471, # person with blond hair
	0x1f472, # man with gua pi mao
	0x1f473, # man with turban
	0x1f474, # older man
	0x1f475, # older woman
	0x1f476, # baby
	0x1f477, # construction worker
	0x1f478, # princess
	0x1f47c, # baby angel
	0x1f481, # information desk person
	0x1f482, # guardsman
	0x1f483, # dancer
	0x1f485, # nail polish
	0x1f486, # face massage
	0x1f487, # haircut
	0x1f4aa, # flexed biceps
	0x1f596, # raised hand with part between middle and ring fingers
	0x1f645, # face with no good gesture
	0x1f646, # face with ok gesture
	0x1f647, # person bowing deeply
	0x1f64b, # happy person raising one hand
	0x1f64c, # person raising both hands in celebration
	0x1f64d, # person frowning
	0x1f64e, # person with pouting face
	0x1f64f, # person with folded hands
	0x1f6a3, # rowboat
	0x1f6b4, # bicyclist
	0x1f6b5, # mountain bicyclist
	0x1f6b6, # pedestrian
	0x1f6c0, # bath
	0x1f6c5, # left luggage

	# added in OSX 10.11.1 / iOS 9.1
	0x26f9, # person-with-ball
	0x270d, # writing-hand
	0x1f3cb, # weight-lifter
	0x1f590, # raised-hand-with-fingers-splayed
	0x1f595, # reversed-hand-with-middle-finger-extended
	0x1f918, # sign-of-the-horns
);

for my $cp(@skin_ids){
	for my $tone(@skin_tones){
		push @ligatures, [$cp, $tone];
	}
}


#
# for flags, we'll just pair up all the flag letters a-z (for 26 x 26 = 676 combinations)
#

for my $a(0..25){
for my $b(0..25){
	push @ligatures, [0x1f1e6+$a, 0x1f1e6+$b];
}
}


#
# ok, time to process those ligatures.
# we wont find all of them (especially the flags), but that's fine
#

for my $lig(@ligatures){
	my $glyph = $f->{'morx'}->resolve_ligature($lig);
	if ($glyph){
		my @components;
		for my $cp(@{$lig}){
			push @components, sprintf('%04x', $cp);
		}
		my $key = ''.$glyph;
		my $path = join('-', @components).'.png';

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
			my $line = `grep -E "^$up" ../UnicodeData.txt`;
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
