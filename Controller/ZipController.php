<?php

namespace itze88\ZipBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use DateTime;

class ZipController extends Controller
{

    public $centralDirectory = array(); // central directory
    public $endOfCentralDirectory = "\x50\x4b\x05\x06\x00\x00\x00\x00"; //end of Central directory record
    public $oldOffset = 0;

    protected $fileHandle;
    protected $compressedDataLength = 0;
    
    
    /**
    * Creates a new ZipFile object
    * 
    * @param resource $_fileHandle file resource opened using fopen() with "w+" mode
    * @return ZipFile
    */
    function setOutputFile($outputFile) {
        $this->fileHandle = fopen($outputFile, "wb");
    }


    /**
        * Creates a new folder in the zip
        *
        * @param string $directoryName folder full path to the .zip root, e.g. "/qqq/www"
        * @return void
        */	
    public function addDir($directoryName) {
            $directoryName = str_replace("\\", "/", $directoryName);

            $feedArrayRow = "\x50\x4b\x03\x04";
            $feedArrayRow .= "\x0a\x00";
            $feedArrayRow .= "\x00\x00";
            $feedArrayRow .= "\x00\x00";
            $feedArrayRow .= "\x00\x00\x00\x00";
            $feedArrayRow .= pack("V",0);
            $feedArrayRow .= pack("V",0);
            $feedArrayRow .= pack("V",0);
            $feedArrayRow .= pack("v", strlen($directoryName));
            $feedArrayRow .= pack("v", 0 );
            $feedArrayRow .= $directoryName;
            $feedArrayRow .= pack("V",0);
            $feedArrayRow .= pack("V",0);
            $feedArrayRow .= pack("V",0);

            fwrite($this->fileHandle, $feedArrayRow);
            $this->compressedDataLength += strlen($feedArrayRow);
            $newOffset = $this->compressedDataLength;

            $addCentralRecord = "\x50\x4b\x01\x02";
            $addCentralRecord .="\x00\x00";
            $addCentralRecord .="\x0a\x00";
            $addCentralRecord .="\x00\x00";
            $addCentralRecord .="\x00\x00";
            $addCentralRecord .="\x00\x00\x00\x00";
            $addCentralRecord .= pack("V",0);
            $addCentralRecord .= pack("V",0);
            $addCentralRecord .= pack("V",0);
            $addCentralRecord .= pack("v", strlen($directoryName) );
            $addCentralRecord .= pack("v", 0 );
            $addCentralRecord .= pack("v", 0 );
            $addCentralRecord .= pack("v", 0 );
            $addCentralRecord .= pack("v", 0 );
            $addCentralRecord .= pack("V", 16 );
            $addCentralRecord .= pack("V", $this->oldOffset );
            $this->oldOffset = $newOffset;
            $addCentralRecord .= $directoryName;
            $this->centralDirectory[] = $addCentralRecord;
    }

    /**
        * Adds a new file to the .zip in the specified .zip folder - previously created using addDir()!
        *
        * @param string $directoryName full path of the previously created .zip folder the file is inserted into
        * @param string $filePath full file path on the disk
        * @return void
        */	
    public function addFile($filePath, $directoryName)   {

            // reading content into memory
            $data = file_get_contents($filePath);

            // create some descriptors
            $directoryName = str_replace("\\", "/", $directoryName);
            $feedArrayRow = "\x50\x4b\x03\x04";
            $feedArrayRow .= "\x14\x00";
            $feedArrayRow .= "\x00\x00";
            $feedArrayRow .= "\x08\x00";
            $feedArrayRow .= "\x00\x00\x00\x00";
            $uncompressedLength = strlen($data);

            // compression of the data
            $compression = crc32($data);
            // at this point filesize*2 memory is required for a moment but it will be released immediatelly
            // once the compression itself done
            $data = gzcompress($data);
            // manipulations
            $data = substr($data, 2, strlen($data) - 6);


            // writing some info
            $compressedLength = strlen($data);
            $feedArrayRow .= pack("V",$compression);
            $feedArrayRow .= pack("V",$compressedLength);
            $feedArrayRow .= pack("V",$uncompressedLength);
            $feedArrayRow .= pack("v", strlen($directoryName) );
            $feedArrayRow .= pack("v", 0 );
            $feedArrayRow .= $directoryName;
            fwrite($this->fileHandle, $feedArrayRow);
            $this->compressedDataLength += strlen($feedArrayRow);

            // writing out the compressed content
            fwrite($this->fileHandle, $data);
            $this->compressedDataLength += $compressedLength;

            // some more info...
            $feedArrayRow = pack("V",$compression);
            $feedArrayRow .= pack("V",$compressedLength);
            $feedArrayRow .= pack("V",$uncompressedLength);
            fwrite($this->fileHandle, $feedArrayRow);
            $this->compressedDataLength += strlen($feedArrayRow);
            $newOffset = $this->compressedDataLength;

            // adding entry
            $addCentralRecord = "\x50\x4b\x01\x02";
            $addCentralRecord .="\x00\x00";
            $addCentralRecord .="\x14\x00";
            $addCentralRecord .="\x00\x00";
            $addCentralRecord .="\x08\x00";
            $addCentralRecord .="\x00\x00\x00\x00";
            $addCentralRecord .= pack("V",$compression);
            $addCentralRecord .= pack("V",$compressedLength);
            $addCentralRecord .= pack("V",$uncompressedLength);
            $addCentralRecord .= pack("v", strlen($directoryName) );
            $addCentralRecord .= pack("v", 0 );
            $addCentralRecord .= pack("v", 0 );
            $addCentralRecord .= pack("v", 0 );
            $addCentralRecord .= pack("v", 0 );
            $addCentralRecord .= pack("V", 32 );
            $addCentralRecord .= pack("V", $this->oldOffset );
            $this->oldOffset = $newOffset;
            $addCentralRecord .= $directoryName;
            $this->centralDirectory[] = $addCentralRecord;

    }

    /**
        * Close the .zip - we do not add more stuff
        *
        * @param boolean $closeFileHandle if true the file resource will be closed too
        */
    public function close($closeFileHandle = true) {

            $controlDirectory = implode("", $this->centralDirectory);

            fwrite($this->fileHandle, $controlDirectory);
            fwrite($this->fileHandle, $this->endOfCentralDirectory);
            fwrite($this->fileHandle, pack("v", sizeof($this->centralDirectory)));
            fwrite($this->fileHandle, pack("v", sizeof($this->centralDirectory)));
            fwrite($this->fileHandle, pack("V", strlen($controlDirectory)));
            fwrite($this->fileHandle, pack("V", $this->compressedDataLength));
            fwrite($this->fileHandle, "\x00\x00");

            if($closeFileHandle)
                    fclose($this->fileHandle);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    function saveFile($directory, $file) {
        // ToDo: Include Save to File Method
        $content = $this->getFile();
        
        return true;
    }
    
    function getFiles() {
        // ToDo: Include getFiles Array Method
        
        return array();
    }
    
    function removeFile($file) {
        // ToDo: Include removeFile Method
    }
    
    function addDirectory($directory, $subDirectory = '', $mainDirectory = '') {
        // Öffnen eines bekannten Verzeichnisses und danach seinen Inhalt einlesen
        if (substr($directory, -1, 1) == "/") {
            if (is_dir($directory)) {
                if ($dh = opendir($directory)) {
                    if ($mainDirectory) {
                        $this->addDir($mainDirectory);
                    }
                    while (($file = readdir($dh)) !== false) {
                        // ToDo: Get exclusions from config
                        if (!in_array($file, array('.', '..'))) {
                            if (is_dir($directory.$file)) {
                                $this->addDir($mainDirectory.$subDirectory.$file."/");
                                $this->addDirectory($directory.$file.'/', $mainDirectory.$subDirectory.$file."/");
                            } elseif (is_file($directory.$file)) {
                                //$content = implode("",file($directory.$file));
                                //$this->addFile($content, $mainDirectory.$subDirectory.$file);
                                $this->addFile($mainDirectory.$subDirectory.$file, $directory.$file);
                                unset($content);
                            }
                        } else {
                            //file excluded
                        }
                    }
                    closedir($dh);
                }
            } else {
                return 'ERROR: "'.$directory.'" ist not a directory!';
            }
        } else {
            return 'ERROR: "'.$directory.'" must endup with "/"';
        }
    }
    
}
