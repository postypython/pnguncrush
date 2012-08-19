pnguncrush
==========

Revert Apple png optimization used to create .ipa packages. Uncrushed file does not keep multiple IDAT chunks.

Sampe Usage
===========

1) Conversion to string:

	$decoder = new PPngUncrush('/tmp/crushed.png');

	$img = $decoder->decodeTo(PPngUncrush::DECODE_TO_STRING);
	

2) Conversion to file

	$decoder = new PPngUncrush('/path/to/source/crushed.png');

	$decoder->decodeTo(PPngUncrush::DECODE_TO_FILE, '/path/to/destination/uncrushed.png);