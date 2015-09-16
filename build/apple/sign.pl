#!/bin/perl

&proc(0x3FFFFFE7 & 0x3FFFFFFF);
&proc(0x3FFFFFED & 0x3FFFFFFF);
&proc(2147483747 & 0x3FFFFFFF);
&proc(83 & 0x3FFFFFFF);

sub proc {
	my ($num) = @_;

	my $last = 0x20000000 & $num;
	if ($last){
		$num = $num | 0xC0000000;
	}

	$num = unpack('l', pack('L', $num));

	print "num = $num\n";
}
