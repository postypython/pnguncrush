<?php
class PPngUncrush {

	const DECODE_TO_FILE = 0;
	
	const DECODE_TO_STRING = 1;
	
	private $_filePath;		
	
	public function __construct($_filePath)
	{
		if ($path !== null) {
			$this->_filePath = $path;
		}
	}		
	
	public function decodeTo($dest, $path=null)
	{
		$img = $this->decode();
		
		if ($dest === PPngUncrush::DECODE_TO_STRING){
		
			return $img;
			
		} else if ($dest === PPngUncrush::DECODE_TO_FILE){
			
			if ($path !== null){
				
				file_put_contents($path, $img);
				
			}
			
		}
		
	}
	
	/**
	 * Decodes Apple optimized png
	 * @return string $imageData
	 */
	private function decode()
	{
			 	
		$fh = fopen($this->_imagePath, 'r');
				
		$headerData = fread ($fh, 8);
		
		$header = unpack ("C1highbit/A3signature/C2lineendings/C1eof/C1eol", $headerData);

		// check if it's a PNG image
		if (! is_array ($header) && ! $header['highbit'] == 0x89 && ! $header['signature'] == "PNG") {
		    return false;
		}

		$chunks = array();
		$isIphoneCompressed = false;
		$cnt=0;
		$uncompressed = '';
		while (! feof($fh)) {
		    $data = fread ($fh, 8);
		    if (strlen($data) > 0) { // Fix for empty parts        
		        // Unpack the chunk
		        $chunk = unpack ("N1length/A4type", $data); // get the type and length of the chunk
		        $data = @fread ($fh, $chunk['length']); // can be 0...	       		
	       		$dataCrc = fread ($fh, 4); // get the crc
		        $crc = unpack ("N1crc", $dataCrc);
		        $chunk['crc'] = $crc['crc'];
        
		        // This chunk is first when it's a iPhone compressed image
		        if ($chunk['type'] == 'CgBI') {
		            $isIphoneCompressed = true;
        		}
        
		        // Extract the header if needed
        		if($chunk['type'] == 'IHDR' && $isIphoneCompressed) {
		         
		            $width = unpack('N*', substr($data, 0, 4));  
        		    $height = unpack('N*', substr($data, 4, 4));
		            $width = $width[1];
        		    $height = $height[1];
					
					$depth = unpack('C1', substr($data, 8, 1));
			 		$depth = $depth[1];			 		
			 					
					$ctype = unpack('C1', substr($data, 9, 1));					
					$ctype = $ctype[1];
					
					$compression = unpack('C1', substr($data, 10, 1));					
					$compression = $compression[1];
					
					$filter = unpack('C1', substr($data, 11, 1));					
					$filter = $filter[1];
					
					$interlace = unpack('C1', substr($data, 11, 1));					
					$interlace = $interlace[1];
					 
		        }
        		
        		// Extract and mutate the data chunk if needed (can be multiple)
		        if ($chunk['type'] == 'IDAT' && $isIphoneCompressed) {
    		         
					$uncompressed .= gzinflate($data);
			      	
				} 
					
 							        
        		$chunk['data'] = $data;
        
		        // Add the chunk to the chunks array so we can rebuild the thing
        		$chunks[] = $chunk;
					
		    }
		}
	
	 	// IHDR
		$out = $headerData;
		$out .= pack('N', $chunks[1]['length']);
		$out .= $chunks[1]['type'];
        $out .= $chunks[1]['data'];
		$out .= pack('N', $chunks[1]['crc']);
		
		// data stream
		$newData = '';
		for ($y=0; $y < $height; $y++) {
		        $i = strlen($newData); // setting the offset
		        $newData .= $uncompressed[$i]; // inject the first pixel, don't know why...
				for ($x=0; $x < $width; $x++) {
				         $i = strlen($newData); // setting the offset
		            // Now we need to swap the BGRA to RGBA
					$newData .= $uncompressed[$i+2]; // Place the Red pixel
					$newData .= $uncompressed[$i+1]; // Place the Green pixel
					$newData .= $uncompressed[$i+0]; // Place the Blue pixel
					$newData .= $uncompressed[$i+3]; // Place the Aplha byte
				 }
				
         }
				
		$compressed = gzcompress($newData);			
		$out .= pack('N', strlen($compressed));
		$out .= 'IDAT';
        $out .= $compressed;
		$out .= pack('N', 'IDAT' . crc32($compressed));
		
		// IEND
		$out .= pack('N', 0);
		$out .= 'IEND';     
		$out .= pack('N', 'IEND' .  crc32(null));
		 	
		 
		return $out;
		
	}

}