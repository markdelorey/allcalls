<?php

// MAKE IT WORK
ini_set('memory_limit', '8192M');
ini_set('auto_detect_line_endings', true);

function array_insert(&$array, $insert, $position = -1) {
     $position = ($position == -1) ? (count($array)) : $position ;
     if($position != (count($array))) {
          $ta = $array;
          for($i = $position; $i < (count($array)); $i++) {
               if(!isset($array[$i])) {
                    die(print_r($array, 1)."\r\nInvalid array: All keys must be numerical and in sequence.");
               }
               $tmp[$i+1] = $array[$i];
               unset($ta[$i]);
          }
          $ta[$position] = $insert;
          $array = $ta + $tmp;
          //print_r($array);
     } else {
          $array[$position] = $insert;
     }

     ksort($array);
     return true;
}

// CONVERT THE CALL IQ EXPORT TO IMPORTABLE FORMAT

// SOURCE FILE
$ifile	= 'old_masters.csv';
$ihandle	= fopen($ifile,'r');
// OUTPUT FILE
$ofile	= 'old_masters_out.csv';
$ohandle = fopen($ofile,'w');

// MISSED CALL SCORES FILE
$missfile = 'old_masters_missed_out.csv';
$misshandle = fopen($missfile,'w');

$bad_ps_array = array(
	'12240-1', // Start MarketABusiness from Salesforce
	'12240-2', 
	'12240-3',
	'02576-1',
	'16676-8',
	'01300.12075-3',
	'03295-1',
	'08655-11',
	'13850-2',
	'14600-1',
	'18205-1',
	'16005-1',
	'18410-2',
	'14405-1',
	'03280-1',
	'06600-7',
	'03850-2',
	'08655-10',
	'13980-1',
	'01300.12075-2',
	'22410-17 Dallas',
	'03615-2',
	'01300.12075-4',
	'06940-1',
	'16710-17',
	'03660-3',
	'18050-6',
	'01650.12601-1',
	'03375-4',
	'03375-3',
	'06600-6',
	'20610-1',
	'20800-1',
	'01490-1',
	'01800-2',
	'05010-1a',
	'05010-1b',
	'11250-3',
	'03850-1',
	'04235-1',
	'15750-1',
	'07780-1',
	'03375-2',
	'19396-1',
	'14610-2',
	'03615-3',
	'06495-4',
	'01300.12075-6',
	'19705-1',
	'14610-1',
	'13160-2',
	'18600-2',
	'11215-1',
	'18402-1',
	'12240-3',
	'23125-1',
	'16270-3',
	'03970-2',
	'13160-4',
	'16700-6',
	'05010-2b',
	'03280-2',
	'19400-8',
	'19400-9',
	'15525-4',
	'03500-1',
	'12425-2',
	'23090-2',
	'03270-3',
	'03375-5',
	'12225-2',
	'16005-3',
	'02075-3',
	'16700-7',
	'16700-8',
	'19397-1',
	'18050-2',
	'03375-6',
	'03660-2',
	'13980-2',
	'16710-7',
	'03970-1',
	'03663-1',
	'16670-3',
	'04235-2',
	'03250-2',
	'03615-4',
	'18626-1',
	'22410-1',
	'16005-2',
	'02075-2',
	'09702-4',
	'19400-11',
	'13975-3',
	'16650-3',
	'16674-1',
	'01105-1',
	'16670-2',
	'13975-4',
	'04399-1',
	'03677-1',
	'13975-1',
	'12240-1',
	'15525-3',
	'02275-2',
	'01600-3',
	'23020-1',
	'03273-1',
	'01010-1',
	'01650.11007-1',
	'06600-5',
	'03375-7',
	'03677-2',
	'03677-3',
	'03663-2',
	'18050-3',
	'19400-12',
	'03615-1',
	'06495-3',
	'19658-9',
	'16730-8',
	'13975-2',
	'16700-5',
	'04399-2',
	'09702-1',
	'18603-1',
	'18050-4',
	'02090-4',
	'13350-3b',
	'23020-3',
	'14240-2',
	'16730-11',
	'16730-10',
	'19658-12',
	'16730-9',
	'19640-5',
	'16677-1',
	'16677-2',
	'08795-2',
	'13850-1',
	'01650.13194-1',
	'16650-5',
	'12475-3',
	'19380-1',
	'08795-1',
	'23020-4',
	'18204-1',
	'16730-7',
	'19658-8',
	'02200-1',
	'12240-2',
	'03270-4',
	'03970-3',
	'02090-6',
	'06495-5',
	'02090-5',
	'22305-1',
	'16650-4',
	'03370-1',
	'18410-3',
	'01600-2',
	'19400-10',
	'08505-1',
	'06600-8',
	'14405-2a',
	'14405-2b',
	'22410-3',
	'16650-6',
	'11530-3',
	'11530-2',
	'15740-2',
	'15740-3',
	'13350-3a',
	'13350-4b',
	'01680-2',
	'01698-1',
	'23020-2',
	'13650-6',
	'01300.12075-5',
	'19640-4',
	'20610-2',
	'06495-2',
	'01650.11007-2',
	'08655-7',
	'08655-9',
	'13650-4',
	'01190-2',
	'13650-5',
	'01070-3',
	'10245-2',
	'19750-2',
	'08655-8',
	'01070-2',
	'16050-3',
	'16050-4',
	'02690-1',
	'18600-1',
	'23390-1',
	'09560.14613-1',
	'25050-1',
	'20750-1',
	'02225.06500-2',
	'02225.06500-3',
	'04350-11',
	'09560.03610-6',
	'18202-5.1',
	'02548-2',
	'01698-1',
	'02600-6',
	'05435-3',
	'08040-3',
	'20320-1',
	'05515-4',
	'10296-5',
	'04078-2',
	'02548-3',
	'20750-2',
	'23167-1',
	'16395-2',
	'22360-3', // End MarketABusiness from Salesforce
	'Google Places', // Start MarketABusiness from $ps_error_frequency
	'Google Places (page2)',
	'13975-1,2',
	'Dex/Yahoo',
	'Dex/Yahoo (page2)',
	'16710-7 thru -16',
	'Dent Asst. ValPak',
	'16710-17 thru -32',
	'16730-18',
	'Website Phone Number',
	'01300.03475-3',
	'01300.12450-3',
	'16730-1', // 2011 MarketABusiness
	'13650-8', // 2011 MarketABusiness
	'ValPak',
	'09702-1,2,3', // 2011 MarketABusiness
	'19658-4,5,6', // 2011 MarketABusiness
	'16730-3', // 2011 MarketABusiness
	'16730-4', // 2011 MAB
	'18626-1,2,3',
	'16730-12',
	'16730-19',
	'16730-6',
	'16730-5',
	'01300.13110-1-2',
	'19658-1,2,3',
	'01300.15750-4-5b',
	'Facebook Ads',
	'01680-1'
	
	//'01300.12405-1-8], // Not in Call IQ Export
	//'01300.12405-1-8b], // Not in Call IQ Export
	//'23090-1', // Split between Call IQ and MarketABusiness
	//'19850-1', // This one is all messed up.. and old and a lost client
	//'08380-1', // Split
	//
	
);

$count = 0;
$badscorecount = 0;
$missedcallcount = 0;
$blankopcount = 0;
$outcount = 0;

while( !feof($ihandle) ) {
	
	$count++;

	$row = '';

	$row = fgetcsv( $ihandle );
	
	/*
	if( count($row) == 54 ) {
		echo '<pre>';
		print_r($row);
		echo '</pre>';
	}
	*/
	
	// OPPORTUNITY - $row[0]
	$opportunity = '';
	$opportunity = $row[0];
	
	// CALL DATE - $row[1] and $row[2]
	$remove_from_date = array(
		'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','@','&','at','.','and',';','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','_','?',',','('
	);
	$orig_appt_date = $row[43];
	$row[43] = str_replace(array('-','//'),'/',str_replace($remove_from_date,' ',$row[43]));
	if( count(explode(' ',$row[43]) ) > 1 ) {
		$appt_date_arr = explode( ' ', $row[43] );
		$row[43] = str_replace(',','',$appt_date_arr[0]);
		if( substr($row[43], -1) == '/' ) {
			$row[43] = substr( $row[43], 0, -1 );
		}
	}
	$call_date = '';
	$call_date = $row[1];
	$call_time = '';
	$call_time = $row[2];
	$date_str = '';
	$date_str = $call_date.' '.$call_time;
	$date = '';
	$date = floor(strtotime($date_str)/60)*60;
	
	$date_key = '';
	$date_key = date( 'mdY', $date );
	
	// CALLER NAME - $row[3]
	$caller_name = '';
	$caller_name = $row[3];
	
	// CALLER'S PHONE NUMBER - $row[4]
	$caller_number = '';
	$caller_number = $row[4];
	
	$caller_key = '';
	$caller_key = strtolower( str_replace( '-', '', $caller_number ) );
	
	// UNUSED ROWS - $row[5] to $row[10]
	
	// NUMBER OF RINGS - $row[11]
	$rings = $row[11];
	
	// CALL SCORE
	$identified_self 		= ($row[12] != '') ? $row[12] : '0';
	$identified_practice 	= ($row[13] != '') ? $row[13] : '0';
	$how_help_you 			= ($row[14] != '') ? $row[14] : '0';
	// $row[15] is blank in standard template
	$asked_name 			= ($row[16] != '') ? $row[16] : '0';
	$best_number 			= ($row[17] != '') ? $row[17] : '0';
	$how_practice_found 	= ($row[18] != '') ? $row[18] : '0';
	$used_please 			= ($row[19] != '') ? $row[19] : '0';
	$used_thank_you 		= ($row[20] != '') ? $row[20] : '0';
	$notes 					= ($row[21] != '') ? $row[21] : '0';
	// $row[22] is blank in standard template
	$friendly_tone 			= ($row[23] != '') ? $row[23] : '0';
	$enthusiastic_tone 		= ($row[24] != '') ? $row[24] : '0';
	$caller_addressed 		= ($row[25] != '') ? $row[25] : '0';
	$spoke_clearly 			= ($row[26] != '') ? $row[26] : '0';
	$hold_permission 		= ($row[27] != '') ? $row[27] : '0';
	$hold_gt_ten 			= ($row[28] != '') ? $row[28] : '0';
	$no_interruptions 		= ($row[29] != '') ? $row[29] : '0';
	// $row[30] is blank in standard template
	$restated_questions		= ($row[31] != '') ? $row[31] : '0';
	$validated_questions	= ($row[32] != '') ? $row[32] : '0';
	$answered_completely	= ($row[33] != '') ? $row[33] : '0';
	$answered_confidently	= ($row[34] != '') ? $row[34] : '0';
	$minimum_pricing		= ($row[35] != '') ? $row[35] : '0';
	$more_questions			= ($row[36] != '') ? $row[36] : '0';
	// $row[37] is blank in standard template
	$maintained_control 	= ($row[38] != '') ? $row[38] : '0';
	$asked_appt				= ($row[39] != '') ? $row[39] : '0';
	$two_appt_choices		= ($row[40] != '') ? $row[40] : '0';
	$appt_scheduled			= ($row[41] != '') ? $row[41] : '0';
	$appt_quantity			= ($row[42] != '') ? $row[42] : '0';
	
	$appt_date				= ($row[43] != '' || $row[43] == '1' || $row[43] == '0' ) ? date( 'n/j/Y', strtotime($row[43]) ) : '';
	// $row[44] is blank in standard template
	$appt_date_remind		= ($row[45] != '') ? $row[45] : '0';
	$patient_excited		= ($row[46] != '') ? $row[46] : '0';
	$thanked_caller			= ($row[47] != '') ? $row[47] : '0';
	$caller_hung_up			= ($row[48] != '') ? $row[48] : '0';
	// $row[49] is blank in standard template
	$prospect_stories		= ($row[50] != '') ? $row[50] : '0';
	$prospect_laugh			= ($row[51] != '') ? $row[51] : '0';
	$adapted_info			= ($row[52] != '') ? $row[52] : '0';
	$special_offer			= ($row[53] != '') ? $row[53] : '0';
	
	$error = false;
	
	if( ( $row[8] == '1' || $row[6] == '0' ) && $row[13] != '1' && $row[13] != '0' ) {
		$missedcallcount++;
		$error = 'missedtest';
		fputcsv($misshandle,$row);
	}
	/*
	if( 
		$row[15] != '' || // Validate blank fields
		$row[22] != '' ||
		$row[30] != '' ||
		$row[37] != '' ||
		$row[44] != '' ||
		$row[49] != ''
	) {
		$badscorecount++;
		$error = 'badps';
	}
	
	if( $opportunity == '' ) {
		$blankopcount++;
		$error = 'blankop';
	}*/

	if( !in_array($opportunity, $bad_ps_array) ) {
	if( !$error) {
		$out_array = array();
		$out_array = array(
			$date . $caller_key, // 0
			$opportunity,
			$date_str,
			$rings, // 2
			// CALL SCORE
			$identified_self, // 3
			$identified_practice, // 4
			$how_help_you, // 5
			// $row[15] is blank in standard template
			$asked_name, // 6
			$best_number, // 7
			$how_practice_found, // 8
			$used_please, // 9
			$used_thank_you, // 10
			$notes, // 11
			// $row[22] is blank in standard template
			$friendly_tone, // 12
			$enthusiastic_tone, // 13
			$caller_addressed, // 14
			$spoke_clearly, // 15
			$hold_permission, // 16
			$hold_gt_ten, // 17
			$no_interruptions, // 18
			// $row[30] is blank in standard template
			$restated_questions, // 19
			$validated_questions, // 20
			$answered_completely, // 21
			$answered_confidently, // 22
			$minimum_pricing, // 23
			$more_questions, // 24
			// $row[37] is blank in standard template
			$maintained_control, // 25
			$asked_appt, // 26
			$two_appt_choices, // 27
			$appt_scheduled, // 28
			$appt_quantity, // 29
			$appt_date, // 30
			// $row[44] is blank in standard template
			$appt_date_remind, // 31
			$patient_excited, // 32
			$thanked_caller, // 33
			$caller_hung_up, // 34
			// $row[49] is blank in standard template
			$prospect_stories, // 35
			$prospect_laugh, // 36
			$adapted_info, // 37
			$special_offer // 38
		);
		$outcount++;
		fputcsv($ohandle,$out_array);
			
	} else {
		$out_array = array();
		$out_array = array(
			$date . $caller_key,
			$error,
			$opportunity
		);
	}
	
	//echo '<pre>';
	//print_r($out_array);
	//echo '</pre>';
	
	}
	
}

echo '<h2>Missed Score Count: '.$missedcallcount.'</h2>';
echo '<h2>Bad Score Count: '.$badscorecount.'</h2>';
echo '<h2>Blank Op Count: '.$blankopcount.'</h2>';
echo '<h2>Out Count: '.$outcount.'</h2>';

fclose( $ohandle );
fclose( $ihandle );