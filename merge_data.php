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

// CONVERT THE CALL IQ EXPORT TO IMPORTABLE FORMAT

// SCORES FILE
$scoresfile	= 'old_masters_out.csv';
$scoreshandle	= fopen($scoresfile,'r');

// CALL IQ RECORDS
$iqfile = 'allcalls-out.csv';
$iqhandle = fopen($iqfile,'r');

// OUTPUT FILE
$ofile	= 'all_data_out.csv';
$ohandle = fopen($ofile,'w');

// OUTPUT FILE
$unfile	= 'unmatched-out.csv';
$unhandle = fopen($unfile,'w');

$iqcount = 0;
$dupcount = 0;
$iqrecords = array();
$dupkeys = array();

$ps_error_frequency = array();
$number_error_frequency = array();

while( !feof($iqhandle) ) {

	$row = '';

	$row = fgetcsv( $iqhandle );
	
	if( isset($iqrecords[$row[0]] ) ) {
		if( !isset($dupkeys[$row[0]]) ) {
			$dupkeys[$row[0]][] = $iqrecords[$row[0]];
		}
		$dupkeys[$row[0]][] = $row;
		$iqcount++;
		$dupcount++;
		unset($iqrecords[$row[0]]);
	} else {
		$iqrecords[$row[0]] = $row;
		$iqcount++;
	}
		
}

echo '<h2>'.(count($iqrecords)+count($dupkeys)).'</h2>';

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

while( !feof($scoreshandle) ) {
	
	$scorescount++;
	
	$row = '';
	
	$row = fgetcsv( $scoreshandle );
	
	if( isset($dupkeys[$row[0]]) ) {
		
		for( $y = 0; $y < count($dupkeys[$row[0]]); $y++ ) {
			if( $dupkeys[$row[0]][$y][6] == 'Prospect' ) {
				$matchcount++;
				if( !in_array($row[1], $scoreerrors) ) {
					foreach($row as $score){
						$dupkeys[$row[0]][$y][] = $score;
					}
				}
			} else {
				$dupkeys[$row[0]][$y][] = $row[0];
				$matchmisscount++;
			}
		}
		
	} else {
	
		if( isset($iqrecords[$row[0]]) ) { // Check for a key match
			
			if( $iqrecords[$row[0]][6] == 'Prospect' ) { // Check if the call was a Prospect
				$matchcount++;
				
				if( !in_array($row[1], $scoreerrors) ) {
					foreach($row as $score){
						$iqrecords[$row[0]][] = $score;
					}
				}			
			} else { // Handle matched calls that were Missed or Unanswered
				$matchmisscount++;
				$iqrecords[$row[0]][] = $row[0];
			}
		} else {
			// [102] - Restricted vs. Number
			// [113] - Malformed date string in old master (;)
			// [131] - Disagreement in times
			// [138] - AM/PM disagreement
			// [175] - Disagreement in date (day: Oct 4 vs Oct 24)
			
			$unmatchedcount++;
			
			fputcsv($unhandle,$row);
			
			/*
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
			*/
			
		}
		
	}
	
}
echo '<pre>';
print_r($dupkeys);
echo '</pre>';

$totalout = 0;
foreach( $iqrecords as $callrecord ) {
	fputcsv($ohandle,$callrecord);
	$totalout++;
}
foreach( $dupkeys as $key => $callrecords ) {
	foreach($callrecords as $callrecord) {
		fputcsv($ohandle,$callrecord);
		$totalout++;
	}
}
	/*
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
	*/
	
	echo '<h2 id="matched">'.$matchcount.'</h2>';
	//echo '<pre>';
	//print_r($matches);
	//echo '</pre>';
	echo '<h2 id="unmatched">'.$unmatchedcount.'</h2>';
	//echo '<pre>';
	//print_r($unmatched);
	//echo '</pre>';
	//echo '<h2 id="frequency">'.count($ps_error_frequency).'</h2>';
	//echo '<pre>';
	//print_r($ps_error_frequency);
	//echo '</pre>';
	//echo '<h2 id="maberror">'.$mab_error_count.'</h2>';
	//echo '<pre>';
	//print_r($mab_error_frequency);
	//echo '</pre>';
	//echo '<h2 id="usererror">'.$user_error_count.'</h2>';
	//echo '<pre>';
	//print_r($user_error_frequency);
	//echo '</pre>';
	echo '<h2 id="missed">'.$matchmisscount.'</h2>';
	//echo '<pre>';
	//print_r($matchedmissed);
	//echo '</pre>';
	echo '<h2 id="total">Scored records: '.$scorescount.'</h2>';
	echo '<h2>Call IQ records: '.$iqcount.'</h2>';
	echo '<h2>Total out records: '.$totalout.'</h2>';

fclose( $iqhandle );
fclose( $scoreshandle );
fclose( $ohandle );
fclose( $unhandle );
