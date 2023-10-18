<?php
/**
  * Class dealing with BTOR Bill format
  *
  * @author Leo Brown
  *
  */
Class ORBillDecoder{

	var $file;

	// generic record defs
	var $record_types = array(
		'CUSTOMERRECORD' => array(
			1 => 'Record Type',
			2 => 'Geneva customer reference',
			3 => 'Account reference',
			4 => 'Invoice reference',
			5 => 'Bill tax date',
			6 => 'Business name',
			7 => 'Address name',
			8 => 'First line of address',
			9 => 'Post code',
			10 => 'Customer VAT status',
			11 => 'Bill Type',
			12 => 'Bill title or service name'
		),
		'PRODUCTCHARGE' => array(
			1 => 'Record Type',
			2 => 'Product Description',
			3 => 'Product Tariff',
			4 => 'Product Label',
			5 => 'Charge Description',
			6 => 'Charge Reason',
			7 => 'Start Date',
			8 => 'End Date',
			9 => 'First line of address',
			10 => 'Post Code',
			11 => 'CSS/Seibel Job No',
			12 => 'Cust/SP order No/Fault ref No.1/2',
			13 => 'Quantity',
			14 => 'Units',
			15 => 'Unit rate',
			16 => 'Product Rate/Price',
			17 => 'VAT Status',
			18 => 'CSS Account Number',
			19 => 'Prod Type',
			20 => 'OR Service ID',
			21 => 'Circuit ID',
			22 => 'MDF Site',
			23 => 'Room ID',
			24 => 'Service ID',
			25 => 'Event Class',
			26 => 'Event Name',
			27 => 'CBUK reference number',
			28 => 'CLI',
			29 => 'MAC code',
			30 => 'Invoice Id',
			31 => 'TRC Start date time',
			32 => 'Clear code',
			33 => 'TRC description code',
			34 => 'Price list reference',
			35 => 'Price list description',
			36 => 'Unique Price Code',
		),
		'CIRCUITSUMMARY' => array(
			1 => 'Record Type',
			2 => 'Circuit Number',
			3 => 'A End 1141 Code – Site Name',
			4 => 'B End 1141 Code – Site Name',
			5 => 'Customer Order No / Ref No',
			6 => 'Distance (m)',
			7 => 'Provide Order Date',
			8 => 'Cease Date',
			9 => 'Connection Charge',
			10 => 'BPR days',
			11 => 'BPR Rental',
			12 => 'Rental Charge',
			13 => 'Credit Rental',
			14 => 'Total Rental',
			15 => 'Other Charges',
			16 => 'Total Circuit Charges',
		),
		'BILLSUMMARYRECORD' => array(
			1 => 'Record Type',
			2 => 'Net total of total bill charges',
			3 => 'Total VAT due on bil',
			4 => 'Net total of charges, NOT subject to VAT',
			5 => 'Invoice total due including any VAT',
			6 => 'Summary total of all event charges (Connection and others)',
			7 => 'Summary total of all periodic charges (whole period rentals and broken period rentals)',
			8 => 'Summary total of all adjustments',
			9 => 'Summary total of RCS adjustments',
		),
		'EVENT' => array(
			1 => 'Record Type',
			2 => 'Product Description',
			3 => 'Product Tariff Name',
			4 => 'Event Source',
			5 => 'Event Description',
			6 => 'Charge Reason',
			7 => 'Event Date',
			8 => 'End Date',
			9 => 'Address Line 1',
			10 => 'Post Code',
			11 => 'CSS/Seibel Job No',
			12 => 'Cust/SP Order No/Fault No.1/2',
			13 => 'Spare',
			14 => 'Quantity/HDFP air count/Total Billing blocks per month',
			15 => 'Units',
			16 => 'Unit rate',
			17 => 'Event Cost',
			18 => 'VAT Status',
			19 => 'CSS Account Number',
			20 => 'Prod Type',
			21 => 'OR Service ID',
			22 => 'Circuit ID',
			23 => 'MDF Site',
			24 => 'Room ID',
			25 => 'Service ID',
			26 => 'Event Class',
			27 => 'Event Name',
			28 => 'CBUK reference number',
			29 => 'CLI',
			30 => 'MAC code',
			31 => 'Invoice Id',
			32 => 'TRC Start date time',
			33 => 'Clear code',
			34 => 'TRC description code',
			35 => 'Price list reference',
			36 => 'Price list description',

			47 => 'Product Set', // (FTTP/FTTC)
			60 => 'Unique Price Code', // NGAxxx

		),
		'ADJUSTMENTS' => array(
			1 => 'Record Type',
			2 => 'Adjustment Name',
			3 => 'Product Tariff Name',
			4 => 'Adjustment free text field',
			5 => 'Charge Desc/Type',
			6 => 'Charge Reason',
			7 => 'Adjustment Date',
			8 => 'End Date',
			9 => 'Address Line 1',
			10 => 'Post Code',
			11 => 'CSS/Seibel Job No',
			12 => 'Cust/SP Order No/Fault No.1',
			13 => 'Cust/SP Order No/Fault No.2',
			14 => 'Quantity/HDFP air count',
			15 => 'Units',
			16 => 'Unit rate',
			17 => 'Net Value',
			18 => 'VAT Status',
			19 => 'CSS Account Number',
			20 => 'Prod Type',
			21 => 'OR Service ID',
			22 => 'Circuit ID',
			23 => 'MDF Site',
			24 => 'Room ID',
			25 => 'Service ID',
			26 => 'Event Class',
			27 => 'Event Name',
			28 => 'CBUK reference number',
			29 => 'CLI',
			30 => 'MAC code',
			31 => 'Invoice Id',
			32 => 'TRC Start date time',
			33 => 'Clear code',
			34 => 'TRC description code',
			35 => 'Price list reference',
			36 => 'Price list description',
		),
	);

	/**
	  * Load DAT file from disk
	  *
	  */
	function load($file){

		// load file and split lines
		$file = file_get_contents($file);
		$file = explode("\n", $file);
		$file = array_filter($file);

		// split rows
		foreach($file as &$record){
			$record = explode('|', $record);
			$record = array_filter($record);
			$this->labelHeaders($record);
		}

		$this->file = $file;
	}

	/**
	  * Print whole invoice to screen
	  *
	  */
	function toText(){
		$t = '';
		foreach($this->file as $record){
			$t .= $this->recordToText($record) . "";
		}
		print $t;
	}

	/**
	  * Render object as text
	  *
	  */
	function recordToText($r){
		$t.=print_r($r,true);
		$t.=$this->ln();
		return $t;
	}

	/**
	  * Reformat a row based on known headers.
	  * First we define known headers, and then we reformat them
	  *
	  */
	function labelHeaders(&$line){

		// find structure for type
		// for instance $line[0] = CUSTOMERRECORD

		// determine record definition
		$record_type = @$this->record_types[$line[0]];
		if(!$record_type) return false;

		// reformat $line with correct headers on working copy
		$new = array();
		foreach($line as $col_index => $val){

			// if we don't find the column name, name it as an index
			if(!$col_name = @$record_type[$col_index + 1]){
				$new["Column ".($col_index + 1)] = $val;
			}
			else $new[$col_name] = $val;
		}

		// update actual copy with working copy
		$line = $new;
	}

	/**
	  * Helper function for debug
	  *
	  */
	function ln($m=''){
		return $m."\n";
	}

	/**
	  * Helper function for date formatting
	  *
	  */
	function formatDate($in){
		if(strlen($in) == 8){
			return substr($in, 0, 4)."-".substr($in,4,2)."-".substr($in,6,2);
		}
		return $in;
	}

}

?>
