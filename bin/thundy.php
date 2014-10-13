<?php

require_once("multilatex-lib.php");

function displayUsageText(){
		echo "Usage:
		 php thundy.php csv.csv";
}


/*const ID_FIELD = 0;
const MERGE_FIELD = 1;
const EMAIL_FIELD = 2;*/
new Field("ID","ID which is used as a the primary record for the addresses.");
new Field("VIAMAIL","Either 'true' or something else (e.g. 'false'). If the string is not 'true' this records product will not generate an eml-file, but will move the PDF in the subfolder build/LANG (e.g. build/DE).");
new Field("EMAIL","If set an eml file is produced with the document attached.");
new Field("NAME","Name of the person");
new Field("LANG","Language for the templates, replacing %L%.");
new Field("LASTPAY","If set to empty string, invoice will be generated (i.e. delete all for those of which an invoice should be written).");


if (count($argv)!=2) {
	/*print_r($argv);*/displayUsageText(); exit(ABORT_TO_FEW_PARAMETERS);
}

/*const CSV_FILE = 0;
const TEX_FILE = 1;
const MAIL_FILE = 2;*/
new File("csv","adresses in CSV file.",new CSVreader($argv[1]));
//new File("tex","LaTeX document in TEX file.",new TEXreader($argv[2]));
//new File("eml","mail template in EML file.",new EMLreader($argv[3]));

File::openAllFiles();

/*foreach ($NECESSARY_FILES as $k=>$data){
	
}
$csvfile = $argv[1];
$data = readcsv($csvfile);

$mailfile = $argv[2];
$mail = readcsv($mailfile);

$mailfile = $argv[2];
$mail = readcsv($mailfile);

/*
//file not found exceptions
if (fnf){
	echo "Necessary file $necessary not found!";
	exit(ABORT_FILE_NOT_FOUND);
}
*/

$num = File::f(CSV_FILE)->countLines();
$header = File::f(CSV_FILE)->getReader()->getHeader();


$data = File::f(CSV_FILE)->getReader()->getData();


//call thunderbird
//$i=0;
foreach($data as $index => $entry) {
	//if ($i++>10) break;
	$filename = "../build/".Field::getField($index,ID_FIELD).".".Field::getField($index,LANG_FIELD).".eml";
	echo "\n".Field::getField($index,"NAME")." ".Field::getField($index,"VIAMAIL");
	if (Field::getField($index,"VIAMAIL")=="true" && Field::getField($index,"LASTPAY")==="") {//HACK --- TODO improve
		system("thunderbird $filename");
	}
}

?>
