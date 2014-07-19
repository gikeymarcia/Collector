<?php
	function array_combine_safely( $keys, $values ) {
		$diff = count($keys) - count($values);
		while( $diff > 0 ) {
			$values[] = '';
			--$diff;
		}
		$i = 1;
		while( $diff < 0 ) {
			while( in_array( $i, $keys ) ) {
				++$i;
			}
			$keys[] = $i;
			++$diff;
		}
		return array_combine( $keys, $values );
	}
	
	function getFirstLine( $fileName, $testStr, $returnDelimiter = FALSE ) {
		// opens a file, reads the headers, finds the delimiter using the test string, reads the first line under the headers, and forms an associative array.  Returns FALSE if any step fails
		if( !is_file( $fileName ) ) { return FALSE; }
		$file = fopen( $fileName, "r" );
		$headerLine = fgets( $file );
		$testLength = strlen( $testStr );
		if( $headerLine === FALSE  ) { return FALSE; }
		$strpos = strpos( $headerLine, $testStr );
		if( $strpos === FALSE ) { return FALSE; }
		if( strlen( $headerLine ) === $testLength ) {				// this would mean that the file only has one header, the test string.  In this case, the delimiter doesn't matter, and every line is just the data for one field, $row[ $testString ]
			$delim = ',';
		} elseif( $strpos === 0 ) {									// this means that the test string is the beginning of the first line, and we need to look at the character after the test string
			$delim = $headerLine[ $testLength ];
		} else {													// finally, if the test string is not the start of the header line, we can just take the character before the start of the test string
			$delim = $headerLine[ $strpos-1 ];
		}
		$headers = str_getcsv( $headerLine, $delim );
		if( !in_array( $testStr, $headers ) ) { return FALSE; }		// this would mean that, for some reason, our delimiter failed to bring out our test string
		$data = fgetcsv( $file, 0, $delim );
		fclose( $file );
		if( $data === FALSE ) { return FALSE; }
		if( $returnDelimiter ) { return $delim; }
		return array_combine_safely( $headers, $data );
	}
	
	function RangeToArray ($input) {
		$output = array();
		$commaExpl  = explode( ',', trim($input) );
		foreach( $commaExpl as $commaDel ) {
			$hyphExpl	= explode( '-', trim($commaDel) );
			if( count( $hyphExpl ) === 1 ) {
				$hyphExpl[1] = $hyphExpl[0];
			}
			elseif( count( $hyphExpl ) > 2 ) {
				$hyphExpl[1] = $hyphExpl[ count($hyphExpl) - 1 ];	//if, for some silly reason, someone put 1-3-5, this is just 1-5
			}
			$output = array_merge( $output, range( $hyphExpl[0], $hyphExpl[1] ) );
		}
		return $output;
	}
	
	function getHeaders( $fileName, $delim = "\t" ) {
		$file = fopen( $fileName, "r" );
		$headers = fgetcsv( $file, 0, $delim );
		fclose($file);
		if( $headers === FALSE ) { $headers = array(); }
		return $headers;
	}
	
	function wrapLabel( $desc, $checked = TRUE, $radio = FALSE, $name = NULL, $addedDesc = array(), $punct = '' ) {
		if( $name === NULL ) { $name = $desc; }
		$name = strtr( $name, ' ', '_' );
		$name = strtr( $name, '.', '_' );
		$type = $radio ? 'radio' : 'checkbox';
		$value = ($radio OR substr($name, -2)==='[]') ? 'value="'.$desc.'" ' : '' ;
		$check = $checked ? 'checked' : '';
		$added = '';
		if( !is_array( $addedDesc ) ) {
			$addedDesc = array( $addedDesc );
		}
		foreach( $addedDesc as $addDesc ) {
			$added .= '<div>'.$addDesc.'</div>';
		}
		echo '<label class="ChoiceLabel"><div><input type="'.$type.'" name="'.$name.'" '.$value.$check.' /></div><div>'.$desc.$punct.'</div>'.$added.'	</label>';
	}
	
	function createBlock( $name, $choices, $checked = TRUE, $radio = FALSE, $arrayName = FAlSE, $addedDesc = array(), $punct = '', $inputName = NULL ) {
		echo '<div class="BlockOptions">';
		echo '<div class="Head">'.$name.'</div>';
		if( $inputName === NULL ) {
			if( $radio ) {
				$inputName = $name;
			} elseif( $arrayName ) {
				$inputName = $name.'[]';
			}
		} elseif( $arrayName ) {
			$inputName .= '[]';
		}
		foreach( $choices as $i => $choice ) {
			$add = isset( $addedDesc[$i] ) ? $addedDesc[$i] : array();
			$finalName = $inputName ? $inputName : $choice;
			wrapLabel( $choice, $checked, $radio, $finalName, $add, $punct );
		}
		echo '</div>';
	}
	
	function getArrayDims( $arr ) {
		$i = 0;
		while( is_array($arr) ) {
			++$i;
			$arr = array_pop($arr);
		}
		return $i;
	}
	
	function arrayToEcho ($array, $ext = 'txt') {
		$junk = array( '\r\n', '\n' , '\t' , '\r' , chr(10) , chr(13) );
		foreach( $array as &$item ) {
				$item = str_replace($junk, ' ', $item );
		}
		unset($item);
		if( $ext === 'csv' ) {
			$out = fopen('php://output', 'w');
			fputcsv($out, $array);
			fclose($out);
		} elseif( $ext === 'browser' ) {
			foreach( $array as &$item ) {
				$item = strtr( $item, array( '<' => '&lt' ) );
			}
			unset( $item );
			$string = '<tr><td><div class="generateDiv">'.implode( '</div></td><td><div>', $array ).'</div></td></tr>';
			echo $string;
		} elseif( $ext === 'th' ) {
			foreach( $array as &$item ) {
				$item = strtr( $item, array( '<' => '&lt' ) );
			}
			unset( $item );
			$string = '<tr><th><div class="generateDivH">'.implode( '</div></th><th><div>', $array ).'</div></th></tr>';
			echo $string;
		} elseif( $ext === 'thFreeze' ) {
			foreach( $array as &$item ) {
				$item = strtr( $item, array( '<' => '&lt' ) );
			}
			unset( $item );
			$string = '<tr class="thFreeze"><th><div class="generateDivH">'.implode( '</div></th><th><div>', $array ).'</div></th></tr>';
			echo $string;
		} else {
			$string = implode( "\t", $array ) . "\r\n";
			echo $string;
		}
	}
?>
