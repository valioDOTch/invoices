<?php

// http://tex.stackexchange.com/questions/5228/can-one-tex-file-output-to-multiple-pdf-files

const ABORT_TO_FEW_PARAMETERS=-1;
const ABORT_FILE_NOT_FOUND=-2;
const ABORT_INVALID_FILE=-3;

const ID_FIELD = 0;
const MERGE_FIELD = 1;
const EMAIL_FIELD = 2;
const LANG_FIELD = 3;

const CSV_FILE = 0;
const TEX_FILE = 1;
const MAIL_FILE = 2;

class File{
	private $id;
	private $description;
	private $data;
	private $reader;
	static private $files;

    /**
     * @param $id
     * @param $description
     * @param $reader
     */
	function __construct($id,$description,$reader){
		$this->id=$id;
		$this->description=$description;
		$this->reader=$reader;
		self::$files[] = $this;
	}

    /**
     * counts the lines of the file
     * @return int
     */
    function countLines(){
        return count($this->getData());
    }

    /**
     * get id of a file
     * @return mixed
     */
	function getID(){
        return $this->id;
    }

    /**
     * gets the description
     * @return mixed
     */
	function getDescription(){
        return $this->description;
    }

    /**
     * set the
     * @param $new
     */
	function setData($new){
        $this->data=$new;
    }

    /**
     * acquire the data
     * @return mixed
     */
	function getData(){
        return $this->data;
    }

    /**
     * get the file reader
     * @return mixed
     */
	function getReader(){
        return $this->reader;
    }

    /**
     * read a ine
     */
	function read(){
		$t = $this->data;
		echo "*$t*";
		$this->data=$this->reader->read();
		$t = $this->data;
		echo "/$t/";
	//	print_r($this->reader);
	}
    /**
     * quick access function to a specific file
     * @param $fileID
     * @return mixed
     */
	static function f($fileID){
		$f = self::getFiles();
		//echo $fileID;
		return $f[$fileID];
	}

    /**
     * get the files array
     * @return mixed
     */
	static function getFiles(){
		return self::$files;
	}

    /**
     * count the files in the file array
     * @return int
     */
	static function getFilesCount(){
		return count(self::$files);
	}

    /**
     * static function to open up all files
     */
	static function openAllFiles(){
		foreach(self::getFiles() as $file){

			$file->data=$file->read();
			echo " \nopening ";
            print_r($file->reader->filepath);
		}
	}
}



class Field{
	var $id;
	var $description;
	static private $fields;
	function getID(){return $this->id;}
	function getDescription(){return $this->description;}
	function __construct($id,$description){
		$this->id=$id;
		$this->description=$description;
		self::$fields[] = $this;
	}

    /**
     * acquire the fields that are present
     * @return mixed
     */
	static function getFields(){
		return self::$fields;
	}

    /**
     * static function to check whether a field exists
     * @param $n
     * @return bool
     */
	static function fieldExists($n){
		//print_r( $n->getID());
		//print_r(File::f(CSV_FILE));
		return in_array($n->getID(),File::f(CSV_FILE)->getReader()->getHeader()); //self::$fields[$n->getID()];
	}

    /**
     * static function to acquire a field
     * @param $entry
     * @param $n
     * @return mixed
     */
	static function getField($entry,$n){
		//print_r( $n);
		$d = File::f(CSV_FILE)->getReader()->getData();
		return $d[$entry][$n];
	}

    /**
     * //TODO
     * @param $header
     */
	static function checkForFieldsPresent($header){
		foreach(self::getFields() as $field){
			//TODO
		}
	}
}


abstract class FileReader{
	/*private*/var $filepath="";
	function __construct($filepath){
		$this->filepath=$filepath;
	}

    /**
     * gets the filepath
     * @return string
     */
	protected function getFilepath(){
        echo "\nfilepath: ".$this->filepath;
		return $this->filepath;
	}
	/**
     * reads a file and returns the data
     */
	function read(){
        echo "not implemented\n";
    }
}

class CSVReader extends FileReader{
	private $data;
	private $header;

    /**
     * acquire data from a CSV file
     * @return mixed
     */
	function getData(){
		return $this->data;
	}
	/**
     * reads a file and returns the data
     */
	function read(){
		$r = array();
		if (($handle = fopen($this->getFilepath(), "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {

//                while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
	//			print_r("r $data");	
				$r[]=$data;
			}
	//		print_r($r);
			fclose($handle);
		}
		$this->data = $r;
		unset($this->data[0]);
		//get the headrow with the row names
		$this->header=$r[0];
		//reassign keys for values for easier access via $data[FIELDID]
		//print_r($this->data);
		//echo $this->data[0]."......";
		foreach ($this->header as $i=>$e){
			//print_r($i);print_r($e);echo "---\n";
			foreach ($this->data as $j=>$k){
				$this->data[$j][$e]=$this->data[$j][$i];
			}
		}
		//print_r($this->data);
		return $r;
	}
    /**
     * 	//TODO keep this, fix code in other areas
     * @return mixed
     */
	function getHeader(){
		return $this->header;
	}

    /**
     * get entry at a specific position
     * @param $n
     * @return mixed
     */
	function getEntry($n){
		return $this->data[$n];
	}
	
}
    //array fgetcsv ( resource $handle [, int $length = 0 [, string $delimiter = "," [, string $enclosure = '"' [, string $escape = "\\" ]]]] )
    /*
     * Parameters ¶

handle
A valid file pointer to a file successfully opened by fopen(), popen(), or fsockopen().

length
Must be greater than the longest line (in characters) to be found in the CSV file (allowing for trailing line-end characters). It became optional in PHP 5. Omitting this parameter (or setting it to 0 in PHP 5.1.0 and later) the maximum line length is not limited, which is slightly slower.

delimiter
Set the field delimiter (one character only).

enclosure
Set the field enclosure character (one character only).

escape
Set the escape character (one character only). Defaults as a backslash.

Return Values ¶

Returns an indexed array containing the fields read.

Note:
A blank line in a CSV file will be returned as an array comprising a single null field, and will not be treated as an error.
Note: If PHP is not properly recognizing the line endings when reading files either on or created by a Macintosh computer, enabling the auto_detect_line_endings run-time configuration option may help resolve the problem.
fgetcsv() returns NULL if an invalid handle is supplied or FALSE on other errors, including end of file.
     *
     * */



class EMLReader extends FileReader{
	/**
     * reads a file and returns the data
     */
	function read(){
		return file_get_contents($this->getFilepath());
	}
}


class TEXReader extends EMLReader{
}



?>
