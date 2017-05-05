package Font::TTF::TTC;

use strict;
use vars qw(@ISA);
require Font::TTF::Font;

use Data::Dumper;

@ISA = qw(Font::TTF::Font);


sub openCollection
{
	my ($class, $fname) = @_;
	my ($fh);
	my ($self) = {};

	unless (ref($fname))
	{
		$fh = IO::File->new($fname) or return undef;
		binmode $fh;
	} else
	{ $fh = $fname; }

	$self->{' INFILE'} = $fh;
	$self->{' fname'} = $fname;
	$self->{' OFFSET'} = 0;
	bless $self, $class;

	# read the collection header
	my $dat;
	$fh->read($dat, 4);
	return undef unless $dat eq 'ttcf';

	my $header = {};
	$fh->read($dat, 8);
	($header->{'majorVersion'},
	 $header->{'minorVersion'},
	 $header->{'numFonts'}) = unpack('nnN', $dat);

	my @offsets;
	for (my $i=0; $i<$header->{'numFonts'}; $i++){
		$fh->read($dat, 4);
		my ($offset) = unpack('N', $dat);
		push @offsets, $offset;
	}

	$header->{'fonts'} = [];
	foreach my $offset(@offsets){
		$self->{' OFFSET'} = $offset;
		$self->read;
		$self->{'cmap'}->read();
		my $num = scalar keys %{$self->{'cmap'}{'Tables'}[0]{'val'}};
		push @{$header->{'fonts'}}, {
			'offset' => $offset,
			'numMappings' => $num,
		};
	}

	$self->{'collection'} = $header;
	$self;
}

sub readCollection
{
	my ($self, $idx) = @_;

	my $font = $self->{'collection'}->{'fonts'}->[$idx];
	$self->{' OFFSET'} = $font->{'offset'};

	$self->read;
}



1;
