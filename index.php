<?php

// Testing a change to the "proposed" branch

// MAKE IT WORK
ini_set('memory_limit', '8192M');
ini_set('auto_detect_line_endings', true);

// CONVERT THE CALL IQ EXPORT TO IMPORTABLE FORMAT

// SOURCE FILE
$ifile	= 'allcalls.csv';
$ihandle	= fopen($ifile,'r');
// OUTPUT FILE
$ofile	= 'allcalls-out.csv';
$ohandle = fopen($ofile,'w');

// NUMBER FILE
$numfile = 'twilio_numbers.csv';
$numhandle = fopen($numfile,'r');

/*
// BAD NUMBERS FILE
$badfile = 'bad_numbers.csv';
$badhandle = fopen($badfile,'w');
*/


$ps = array();
$dup_ps = array();
$dup_count = 0;
$bad_ps_array = array(
	'12240-1', // MarketABusiness
	'12240-2', // MarketABusiness
	'12240-3' // MarketABusiness
);
$bad_number_array = array();
while ( !feof($numhandle ) ) {
	$row = fgetcsv( $numhandle );
	
	$tn = $row[0];
	
	if( isset( $ps[$tn] ) ) {
		if( !isset( $dup_ps[$tn] ) ) {
			$dup_ps[$tn][] = array(
				'opportunity' => $ps[$tn]['opportunity'],
				'target_date' => $ps[$tn]['target_date']
			);
			echo 'first_time';
			$dup_count++;
		}
		$dup_ps[$tn][] = array( 
			'opportunity' => $row[1],
			'target_date' => strtotime($row[2])
		);
		$dup_count++;
		unset($ps[$tn]);
	} elseif( $row[0] != '' ) {
		$ps[$tn] = array( 
			'opportunity' => $row[1],
			'target_date' => strtotime($row[2])
		);
	}
}

echo '$dup_count ' . $dup_count;
echo '<pre>';
print_r($dup_ps);
echo '</pre>';

$count = 0;

$client_num_errors = 0;
$client_num_error_records = array();

$unassigned_tracking_numbers = array();

$errors = 0;
$error_records = array();

$dn_errors = 0;
$dn_error_records = array();

$switch_count = 0; 


while ( !feof($ihandle ) ) {

	$count++;

	$row = '';

	$row = fgetcsv( $ihandle );
	
	//echo '<pre>';
	//print_r($row);
	// echo '</pre>';
	
	$date = '';
	$date			= floor(strtotime($row[0])/60)*60;
	$date_str = '';
	$date_str		= date( 'n/j/Y H:i', $date );
	$date_key = '';
	$date_key		= date( 'mdY', $date );
	
	
	// CLIENT NUMBER
	$client_number = '';
	$client_number	= $row[1];
	if( $client_number != '' ) {
	
		preg_match( '(\d{11}|\d{10})', $client_number, $matches );
		
		if( count( $matches ) == 1 ) {
			if( strlen( $matches[0] ) == 11 ) {
				$client_number = '+' . $matches[0];
			} else {
				$client_number = '+1' . $matches[0];
			}
			echo '$client_number' . $client_number . '<br />';
			echo '$matches[0] ' . $matches[0] . ' strlen ' . strlen($matches[0]) . '<br />';
			echo 'matched round 1' . '<br />';
			echo 'count = ' . $count . '<br />';
		} else {
			preg_match( '/restricted/i', $client_number, $matches);
			if( count( $matches ) == 1 ) {
				$client_number = 'RESTRICTED';
				echo '$client_number' . $client_number . '<br />';
				echo 'matched round 2' . '<br />';
				echo 'count = ' . $count . '<br />';
			} else {
				preg_match( '/anonymous/i', $client_number, $matches);
				if( count( $matches ) == 1 ) {
					$client_number = 'ANONYMOUS';
					echo '$client_number' . $client_number . '<br />';
					echo 'matched round 3' . '<br />';
					echo 'count = ' . $count . '<br />';
				} else {
					$client_number = 'NOT AVAILABLE';
					echo '$client_number' . $client_number . '<br />';
					echo 'matched round 0' . '<br />';
					echo 'count = ' . $count . '<br />';
					$client_num_errors++;
					$client_num_error_records[] = $count;
				}
			}
		}
	} else {
		$client_number = 'NOT AVAILABLE';
		echo '$client_number' . $client_number . '<br />';
		echo 'matched round 0' . '<br />';
		echo 'count = ' . $count . '<br />';
		$client_num_errors++;
		$client_num_error_records[] = $count;
	}
	
	$caller_key = '';
	$caller_key = strtolower( str_replace( '+1', '', $client_number ) );
	
	
	
	// TRACKING NUMBER
	$tracking_number = '';
	$tracking_number = $row[2];
	if( strlen($tracking_number) == 10 ) {
		$tracking_number = '+1' . $tracking_number;
	} else {
		$tracking_number = '+' . $tracking_number;
	}
	
	
	// OPPORTUNITY NAME
	// Check to see if the number was used more than once
	$opportunity = '';
	if( isset( $dup_ps[$tracking_number] ) ) {
		
		$dup_c = count( $dup_ps[$tracking_number] );
		
		$opportunity = $dup_ps[$tracking_number][0]['opportunity'];
		
		// Switch opportunity based on the calls relationship to the in-home target date
		for( $i = 0; $i < count($dup_ps[$tracking_number]); $i++ ) {
			
			if( $dup_ps[$tracking_number][$i]['target_date'] <= $date ) {
				$opportunity = $dup_ps[$tracking_number][$i]['opportunity'];
			}
			
		}
		
	} 
	// Assign opportunity for 1 to 1 P&S to tracking numbers
	else {
		if( isset( $ps[$tracking_number] ) ) {
			$opportunity = $ps[$tracking_number]['opportunity'];
		} else {
			if( !isset( $unassigned_tracking_numbers[$row[3]] ) ) {
				$unassigned_tracking_numbers[$row[3]] = $tracking_number;
		
				$bad_numbers_array = array(
					$unassigned_tracking_numbers[$row[3]],
					$tracking_number,
				);
		
				fputcsv($badhandle,$bad_numbers_array);
			}
			$error_records[] = $count;
			$errors++;
		}
	}
	
	
	
	// DENTIST NUMBER
	$dentist_number = '';
	$dentist_number = $row[4];
	$dentist_number = trim($dentist_number);
	$dentist_number = substr($dentist_number, -10);
	if( strlen($dentist_number) != 10 && strlen($dentist_number) != 0 ) {
		echo 'not 10<br />';
		$dn_error_records[$count] = $tracking_number;
		$dn_errors++;
	}
	
	
	// CALL TYPE
	$call_status = '';
	$call_status = $row[5];
	$duration = intval($row[6]);
	if( $call_status == 'ANSWERED' ) {
		if( $duration < 41 ) {
			$call_type = 'Missed';
		} else {
			$call_type = 'Prospect';
		}
	} else {
		$call_type = 'Unanswered';
	}
	
	
	
	// CALL RECORDING
	$recording_file = '';
	$recording_file = $row[8];
	$recording_pref = 'http://call-iq-recordings.s3-website-us-east-1.amazonaws.com/';
	if( $recording_file == 'No-Recording-Found' || $recording_file == 'No-Recording-Made' ) {
		$recording = '';
	} else {
		$recording = $recording_pref . $recording_file;
	}
	
	if( 
		$opportunity != '' && 
		!in_array($opportunity, $bad_ps_array) &&
		!in_array(str_replace('+1','',$tracking_number), $bad_number_array)
	) {
		$out_array = array(
			$date.$caller_key,
			$date_str,
			$client_number,
			$tracking_number,
			$opportunity,
			$dentist_number,
			$call_type,
			$duration,
			$recording
		);
		fputcsv($ohandle,$out_array);
	}
	
	
}
/*
echo 'Total DN errors: ' . $dn_errors;
echo '<pre>';
print_r($dn_error_records);
echo '</pre>';
*/
echo 'Total errors: ' . $errors;
echo '<pre>';
print_r($error_records);
echo '</pre>';
echo '<pre>';
print_r($unassigned_tracking_numbers);
echo '</pre>';


fclose( $ohandle );
fclose( $ihandle );
fclose( $numhandle );
//fclose( $badhandle );

?>
