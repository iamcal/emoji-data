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

# keycaps
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

# families
$filenames->{'846'} = '1f468-200d-1f468-200d-1f466.png'; # mmb
$filenames->{'847'} = '1f468-200d-1f468-200d-1f466-200d-1f466.png'; # mmbb
$filenames->{'848'} = '1f468-200d-1f468-200d-1f467.png'; # mmg
$filenames->{'849'} = '1f468-200d-1f468-200d-1f467-200d-1f466.png'; # mmgb
$filenames->{'850'} = '1f468-200d-1f468-200d-1f467-200d-1f467.png'; # mmgg

$filenames->{'851'} = '1f468-200d-1f469-200d-1f466-200d-1f466.png'; # mwbb
$filenames->{'852'} = '1f468-200d-1f469-200d-1f467.png'; # mwg
$filenames->{'853'} = '1f468-200d-1f469-200d-1f467-200d-1f466.png'; # mwgb
$filenames->{'854'} = '1f468-200d-1f469-200d-1f467-200d-1f467.png'; # mwgg

$filenames->{'855'} = '1f469-200d-1f469-200d-1f466.png'; # wwb
$filenames->{'856'} = '1f469-200d-1f469-200d-1f466-200d-1f466.png'; # wwbb
$filenames->{'857'} = '1f469-200d-1f469-200d-1f467.png'; # wwg
$filenames->{'858'} = '1f469-200d-1f469-200d-1f467-200d-1f466.png'; # wwgb
$filenames->{'859'} = '1f469-200d-1f469-200d-1f467-200d-1f467.png'; # wwgg

# couples
$filenames->{'982'} = '1f468-200d-2764-fe0f-200d-1f48b-200d-1f468.png'; # m-kiss-m
$filenames->{'983'} = '1f469-200d-2764-fe0f-200d-1f48b-200d-1f469.png'; # w-kiss-w
$filenames->{'986'} = '1f468-200d-2764-fe0f-200d-1f468.png'; # m-heart-m
$filenames->{'987'} = '1f469-200d-2764-fe0f-200d-1f461.png'; # w-heart-w


# flags are complicated because there are 214 of them, but we currently
# have very few in the actual data set. once i figure out ligatures we
# can use all of them, but for now we'll just do the ones from android.
my %flags = (
	209 => '',
	210 => 'au',
	211 => '',
	212 => '',
	213 => '',
	214 => '',
	215 => '',
	216 => 'at',
	217 => 'ae',
	218 => '',
	219 => '',
	220 => '',
	221 => '',
	222 => '',
	223 => '',
	224 => 'br',
	225 => '',
	226 => '',
	227 => '',
	228 => '',
	229 => '',
	230 => '',
	231 => '',
	232 => 'be',
	233 => '',
	234 => '',
	235 => '',
	236 => '',
	237 => '',
	238 => '',
	239 => '',
	240 => '',
	241 => '',
	242 => '',
	243 => '',
	244 => '',
	245 => '',
	246 => '',
	247 => 'cn',
	248 => '',
	249 => 'cl',
	250 => '',
	251 => 'co',
	252 => '',
	253 => 'ca',
	254 => '',
	255 => '',
	256 => '',
	257 => 'ch',
	258 => 'de',
	259 => '',
	260 => '',
	261 => 'dk',
	262 => '',
	263 => '',
	264 => '',
	265 => 'es',
	266 => '',
	267 => '',
	268 => '',
	269 => '',
	270 => '',
	271 => 'fr',
	272 => 'fi',
	273 => '',
	274 => '',
	275 => '',
	276 => '',
	277 => '',
	278 => '',
	279 => '',
	280 => '',
	281 => '',
	282 => '',
	283 => '',
	284 => '',
	285 => '',
	286 => '',
	287 => '',
	288 => '',
	289 => 'gb',
	290 => '',
	291 => '',
	292 => '',
	293 => 'hk',
	294 => '',
	295 => 'ie',
	296 => 'id',
	297 => '',
	298 => '',
	299 => 'in',
	300 => 'il',
	301 => 'it',
	302 => '',
	303 => '',
	304 => 'jp',
	305 => '',
	306 => '',
	307 => '',
	308 => '',
	309 => '',
	310 => '',
	311 => '',
	312 => '',
	313 => '',
	314 => '',
	315 => 'kr',
	316 => '',
	317 => '',
	318 => '',
	319 => '',
	320 => '',
	321 => '',
	322 => '',
	323 => '',
	324 => '',
	325 => '',
	326 => '',
	327 => '',
	328 => '',
	329 => '',
	330 => 'mo',
	331 => '',
	332 => '',
	333 => '',
	334 => '',
	335 => '',
	336 => '',
	337 => '',
	338 => '',
	339 => '',
	340 => '',
	341 => 'my',
	342 => 'mx',
	343 => '',
	344 => '',
	345 => '',
	346 => '',
	347 => '',
	348 => '',
	349 => 'nl',
	350 => 'no',
	351 => '',
	352 => 'nz',
	353 => '',
	354 => '',
	355 => '',
	356 => '',
	357 => '',
	358 => '',
	359 => '',
	360 => '',
	361 => '',
	362 => 'ph',
	363 => '',
	364 => '',
	365 => '',
	366 => 'pr',
	367 => 'pt',
	368 => 'pl',
	369 => '',
	370 => '',
	371 => '',
	372 => '',
	373 => 'ru',
	374 => '',
	375 => '',
	376 => 'sa',
	377 => '',
	378 => '',
	379 => '',
	380 => 'sg',
	381 => 'se',
	382 => '',
	383 => '',
	384 => '',
	385 => '',
	386 => '',
	387 => '',
	388 => '',
	389 => '',
	390 => '',
	391 => '',
	392 => '',
	393 => '',
	394 => '',
	395 => '',
	396 => '',
	397 => '',
	398 => '',
	399 => '',
	400 => 'tr',
	401 => '',
	402 => '',
	403 => '',
	404 => '',
	405 => '',
	406 => '',
	407 => 'us',
	408 => '',
	409 => '',
	410 => '',
	411 => '',
	412 => '',
	413 => 'vn',
	414 => '',
	415 => '',
	416 => '',
	417 => '',
	418 => '',
	419 => '',
	420 => 'za',
	421 => '',
	422 => '',
);

for my $idx (keys %flags){
	next unless $flags{$idx};
	my @letters = split(//, $flags{$idx});
	my @chars;
	for my $letter(@letters){
		push @chars, sprintf('%x', ord($letter) + 0x1f1e6 - 97);
	}
	my $chars = join '-', @chars;

	$filenames->{$idx} = $chars.'.png';
	#print "flag $idx is $flags{$idx} - $chars\n";
}
#exit;


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

`rm -f ../../img-apple-160/*.png`;

for my $glyph_id(0..$f->{'maxp'}->{'numGlyphs'}-1){

	my $filename = $filenames->{$glyph_id};
	unless ($filename){
		$filename = $glyph_id."_UNKNOWN.png";
	}
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

#$f->{'sbix'}->read();
#print Dumper $f->{'sbix'};

#my $strike = $f->{'sbix'}->read_strike(160, 505);
#print Dumper $f->{'sbix'};
#print Dumper $strike;

#$f->{'maxp'}->read();
#print Dumper $f->{'maxp'};
