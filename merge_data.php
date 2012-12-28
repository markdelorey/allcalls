<a href="#matched">matched</a><br />
<a href="#unmatched">unmatched</a><br />
<a href="#frequency">frequency</a><br />
<a href="#maberror">mab errors</a><br />
<a href="#usererror">user errors</a><br />
<a href="#missed">missed</a><br />

<?php

// MAKE IT WORK
ini_set('memory_limit', '8192M');
ini_set('auto_detect_line_endings', true);

// SCORES FILE - created by /parse_old_masters.php
$scoresfile	= 'old_masters_out.csv';
$scoreshandle	= fopen($scoresfile,'r');

// CALL IQ RECORDS - created by /parse_call_iq_records.php
$iqfile = 'allcalls-out.csv';
$iqhandle = fopen($iqfile,'r');

// MASTER OUTPUT FILE
$ofile	= 'all_data_out.csv';
$ohandle = fopen($ofile,'w');

// UNMATCHED FILE
$unfile	= 'unmatched-out.csv';
$unhandle = fopen($unfile,'w');
/*
// CALL KEYS FILE
$keyfile	= 'keys.csv';
$keyhandle = fopen($keyfile,'w');

// DUPLICATE KEYS FILE
$dupfile	= 'dup_keys.csv';
$duphandle = fopen($dupfile,'w');
*/
$iqrecords = array();
$dupkeys = array();

$ps_error_frequency = array();
$number_error_frequency = array();

//
// Get all of the records from the Call IQ export
//
while( !feof($iqhandle) ) {

	$row = '';

	$row = fgetcsv( $iqhandle );
	
	if( isset($iqrecords[$row[0]] ) ) { // Check if this single record key already exists
		if( !isset($dupkeys[$row[0]]) ) { // Now check if this duplicate record key already exists
			$dupkeys[$row[0]][] = $iqrecords[$row[0]]; // if it doesn't, add the existing single key record as the first value in the duplicate key records array
		}
		$dupkeys[$row[0]][] = $row; // add the current record to the duplicate records
	} else {
		$iqrecords[$row[0]] = $row; // if this record doesn't exist, add it to the single records and increment counter
	}
		
}

//echo '<pre>'.print_r($dupkeys,true).'</pre>';
//echo '<pre>'.print_r($iqrecords,true).'</pre>';

// 
// Remove any records from the single key array that were added to the duplicate key array
//
$dup_count = 0;
foreach( $dupkeys as $key => $calls ) {
	
	if( isset($iqrecords[$key]) ) { // Check if the single key array has a matching key from the duplicate key array
		unset($iqrecords[$key]);
		//echo 'Unset $iqrecords['.$key.']<br />';
	}
	
	foreach( $calls as $call ) { // Now count the calls to check for loss
		$dup_count++;
		
	}
	
}

$key_count = 0;
foreach( $iqrecords as $key => $record ) { // And count the single key array
	$key_count++;
}


$scorescount = 0;
$scorerecords = array();
$matchcount = 0;
$matches = array();
$matchmisscount = 0;
$matchedmissed = array();
$unmatchedcount = 0;
$unmatched = array();

$scoreerrors = array(
	'badps',
	'missedtest',
	'blankop'
);

//
// Open the scores file and match them to Call IQ records
//
while( !feof($scoreshandle) ) {
	
	$scorescount++;
	
	$row = '';
	
	$row = fgetcsv( $scoreshandle );
	
	if( isset($dupkeys[$row[0]]) ) { // check if there is a key match in the duplicate keys array
		
		for( $y = 0; $y < count($dupkeys[$row[0]]); $y++ ) { // loop through the duplicate keys array where the keys match
			if( $dupkeys[$row[0]][$y][7] == 'Prospect' ) { // if the call type is Prospect
				$matchcount++; // increase the match counter
				if( !in_array($row[1], $scoreerrors) ) { // and add the scores on to the end of the duplicate key array record
					foreach($row as $score){
						$dupkeys[$row[0]][$y][] = $score;
					}
				}
			} else { // if the call type is not Prospect
				$dupkeys[$row[0]][$y][] = $row[0]; // add only the key value to the end of the duplicate key array record
				$matchcount++; // increase the match counter
				$matchmisscount++; // increase the matched missed counter
			}
		}
		
	} else { // if the key from the score record doesn't match the duplicate keys array
	
		if( isset($iqrecords[$row[0]]) ) { // Check for a key match in the single keys array
			
			if( $iqrecords[$row[0]][7] == 'Prospect' ) { // Check if the call was a Prospect
				$matchcount++; // increment match counter
				
				if( !in_array($row[1], $scoreerrors) ) { // and add the scores on to the end of the single key array record
					foreach($row as $score){
						$iqrecords[$row[0]][] = $score;
					}
				}			
			} else { // if the call type is not Prospect
				$matchmisscount++; // increment match counter
				$iqrecords[$row[0]][] = $row[0]; // and add the key value to the end of the single key array record
			}
		} else { // if the scores record key doesn't match any record in the single key or duplicate key arrays
		
			// [102] - Restricted vs. Number
			// [113] - Malformed date string in old master (;)
			// [131] - Disagreement in times
			// [138] - AM/PM disagreement
			// [175] - Disagreement in date (day: Oct 4 vs Oct 24)
			
			$unmatchedcount++; // increment the unmatched count
			
			fputcsv($unhandle,$row); // put the record into the unmatched filed
			
			$opportunity = $row[1]; // Track the errors and frequency rates
			
			if( isset($ps_error_frequency[$opportunity]) ) {
				$ps_error_frequency[$opportunity]++;
			} else {
				$ps_error_frequency[$opportunity] = 1;
			}
			
			if( isset($number_error_frequency[$row[5]]) ) {
				$number_error_frequency[$row[5]]++;
			} else {
				$number_error_frequency[$row[5]] = 1;
			}
			
			
		}
		
	}
	
}

$totalout = 0;
// Loop through the single key array
foreach( $iqrecords as $callrecord ) {
	// Unset duplicate or useless values
	unset($callrecord[0]); // key from call iq records
	unset($callrecord[9]); // key from old masters
	unset($callrecord[10]); // opportunity field from old masters
	unset($callrecord[11]); // call date time from old masters
	fputcsv($ohandle,$callrecord); // and add them to the output file
	$totalout++; // increment output counter
}
// Loop through the duplicate key array
foreach( $dupkeys as $key => $callrecords ) {
	// and then loop through the individual records
	foreach($callrecords as $callrecord) {
	// Unset duplicate or useless values
		unset($callrecord[0]); // key from call iq records
		unset($callrecord[9]); // key from old masters
		unset($callrecord[10]); // opportunity field from old masters
		unset($callrecord[11]); // call date time from old masters
		fputcsv($ohandle,$callrecord); // and add them to the output file
		$totalout++; // increment output counter
	}
}

	
	// ATTRIBUTE ERRORS TO MAB OR USER ERROR BASED ON QUANTITY
	asort($ps_error_frequency);
	asort($number_error_frequency);
	
	$user_error_frequency = array();
	$user_error_count = 0;
	
	$mab_error_frequency = array();
	$mab_error_count = 0;
	
	foreach($ps_error_frequency as $ps => $frequency) {
		if( $frequency <= 4 ) {
			$user_error_frequency[$ps] = $frequency;
			$user_error_count = $user_error_count + $frequency;
		} else {
			$mab_error_frequency[$ps] = $frequency;
			$mab_error_count = $mab_error_count + $frequency;
		}
	}
	
	
	echo '<h2 id="matched">Total Matched: '.$matchcount.'</h2>';
	//echo '<pre>';
	//print_r($matches);
	//echo '</pre>';
	echo '<h2 id="unmatched">Unmatched: '.$unmatchedcount.'</h2>';
	//echo '<pre>';
	//print_r($unmatched);
	//echo '</pre>';
	echo '<h2 id="frequency">PS Error'.count($ps_error_frequency).'</h2>';
	echo '<pre>';
	print_r($ps_error_frequency);
	echo '</pre>';
	echo '<h2 id="maberror">Grouped Errors: '.$mab_error_count.'</h2>';
	echo '<pre>';
	print_r($mab_error_frequency);
	echo '</pre>';
	echo '<h2 id="usererror">Isolated Errors: '.$user_error_count.'</h2>';
	echo '<pre>';
	print_r($user_error_frequency);
	echo '</pre>';
	//echo '<pre>';
	//print_r($matchedmissed);
	//echo '</pre>';
	echo '<h2 id="total">Scored records: '.$scorescount.'</h2>';
	echo '<h2>Call IQ records: '.($key_count+$dup_count).'</h2>';
	//echo '<h2>Total out records: '.$totalout.'</h2>';

fclose( $iqhandle );
fclose( $scoreshandle );
fclose( $ohandle );
fclose( $unhandle );
