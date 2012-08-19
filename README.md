pnguncrush
==========

Reverts Apple PNG optimization used to create .ipa packages.
Uncrushed file does not contains multiple IDAT chunks. It is a basic PNG image structured as follows: IHDR, IDAT, IEND;
Based on peperzaken procedure: https://github.com/peperzaken/iPhone-optimized-PNG-reverse-script

Sample Usage
------------

###Conversion to string:

	$decoder = new PPngUncrush('/tmp/crushed.png');

	$img = $decoder->decodeTo(PPngUncrush::DECODE_TO_STRING);
	

###Conversion to file

	$decoder = new PPngUncrush('/path/to/source/crushed.png');

	$decoder->decodeTo(PPngUncrush::DECODE_TO_FILE, '/path/to/destination/uncrushed.png);