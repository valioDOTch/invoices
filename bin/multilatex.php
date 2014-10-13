<?php

require_once("multilatex-lib.php");

function displayUsageText(){
		echo "Usage:
		 php multilatex.php persons.csv template_%L%.tex template_%L%.eml\n";
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



if (count($argv)<4) {
	/*print_r($argv);*/displayUsageText(); exit(ABORT_TO_FEW_PARAMETERS);
} else {
	print_r($argv);
}

/*const CSV_FILE = 0;
const TEX_FILE = 1;
const MAIL_FILE = 2;*/
new File("csv","adresses in CSV file.",new CSVreader($argv[1]));
new File("tex","LaTeX document in TEX file.",new TEXreader($argv[2]));
new File("eml","mail template in EML file.",new EMLreader($argv[3]));

echo "Opening all files\n";
File::openAllFiles();

echo "Opened all files\n";
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

//echo "HEADER: "; print_r($header);


$data = File::f(CSV_FILE)->getReader()->getData();

//echo "DATA: "; print_r($data);


/*
 * check if fields are present
 */

//foreach($header as $entry) {

	//	$header= $line;
		foreach (Field::getFields() as $necessary){
			if (!Field::fieldExists($necessary)){
				$necessaryname = $necessary->getID();
				echo "Necessary csv column not found: $necessaryname in ".$argv[1]."\n";
				
				exit(ABORT_INVALID_FILE);
			}
		}
		//continue;
//	}
//}
//echo "*";print_r($data);echo "*";

//const ID_FIELD = 0;
//const MERGE_FIELD = 1;
//const EMAIL_FIELD = 2;
//const LANG_FIELD = 3;
echo "All CSV columns available\n";

/*
 * writing tex files
 */


chdir("../build/");

$i=0;
foreach($data as $index => $entry) {
//	if ($i++>5) break;
	if (Field::getField($index,"LASTPAY")==="") {//HACK --- TODO improve
	
		$filename = Field::getField($index,ID_FIELD).".".Field::getField($index,LANG_FIELD).".tex";
		//$templatefile="../textpl/SRechnung_".Field::getField($index,LANG_FIELD).".tex";
echo "-----".Field::getField($index,LANG_FIELD).".------";

        $templatefile= str_replace("%L%",Field::getField($index,LANG_FIELD),$argv[2]);

echo "\n".Field::getField($index,LANG_FIELD)."\n";

		//create the address file
		$fp = fopen($filename, "w");
		//$r="";
		$text=file_get_contents($templatefile);
        echo "text $text";
		foreach (File::f(CSV_FILE)->getReader()->getHeader() as $item){
			$e = Field::getField($index,$item);
			//$e = str_replace("@","$@$",$e);
			$text=str_replace("%".$item."%",$e,$text);
			
			//$r.= "$item: ".print_r($e,true)."\n";
			

		}
		fputs ($fp, $text);
		//fputs ($fp, $r);
		fclose ($fp);
		system("pdflatex $filename");
	}
}
//compile the latex
//system("for %i in (*.EN.adr) do pdflatex -jobname=%i SRechnung_EN.tex");
//system("for %i in (*.DE.adr) do pdflatex -jobname=%i SRechnung_DE.tex");
/* You could just have one info-file for each letter

% letter1.adr
\def\toname{Foo}
and

% letter2.adr
\def\toname{Bar}
and then have a main file

% main.tex
\documentclass[addrfield]{scrlttr2}
\input \jobname.adr

\begin{document}
\begin{letter}{\toname}
\opening{Dear \toname, }
 A nice letter.
\end{letter}
\end{document}
Then you can compile all letters from the command line with something like


* 
*/

/*foreach($data as $entry) {
	//if merge is set
	//add to merge list
	
	
}*/
$i=0;
foreach($data as $index => $entry) {
//	if ($i++>5) break;
	if (Field::getField($index,"LASTPAY")==="") {//HACK --- TODO improve
	
		$filename = Field::getField($index,ID_FIELD).".".Field::getField($index,LANG_FIELD).".pdf";
	
	$base64 = base64_encode(file_get_contents($filename));
	$base64=	chunk_split (	$base64 );

		$filename = Field::getField($index,ID_FIELD).".".Field::getField($index,LANG_FIELD).".eml";
		//$templatefile="../mailtpl/pwb_".Field::getField($index,LANG_FIELD).".eml";
        $templatefile= str_replace("%L%",Field::getField($index,LANG_FIELD),$argv[3]);

        //create the address file
		$fp = fopen($filename, "w");
		//$r="";
		$text=file_get_contents($templatefile);
		foreach (File::f(CSV_FILE)->getReader()->getHeader() as $item){
			$e = Field::getField($index,$item);
			//$e = str_replace("@","$@$",$e);
			$text=str_replace("%".$item."%",$e,$text);
			
			//$r.= "$item: ".print_r($e,true)."\n";
			

		}
		$text=str_replace("%base64%",$base64,$text);
		fputs ($fp, $text);
		//fputs ($fp, $r);
		fclose ($fp);
		//system("pdflatex $filename");
	}
}



//then merge
$i=0;
$notviamail="";
foreach($data as $index => $entry) {
	//if ($i++>5) break;
	if (Field::getField($index,"LASTPAY")==="") {//HACK --- TODO improve
		if (Field::getField($index,"VIAMAIL")!="true") {
			$notviamail.="\nNOT VIA MAIL for ".Field::getField($index,"NAME")." but value=".Field::getField($index,"VIAMAIL");
			$filename = Field::getField($index,ID_FIELD).".".Field::getField($index,LANG_FIELD);
			system("mv $filename.pdf ". Field::getField($index,LANG_FIELD)."/$filename.pdf");
			system("rm $filename.eml");
			//system("mv $filename.eml ". Field::getField($index,LANG_FIELD)."/$filename.eml");
		} else {
			echo "\n via eMAIL for ".Field::getField($index,"NAME");
		}
	}
}
echo $notviamail;

?>
