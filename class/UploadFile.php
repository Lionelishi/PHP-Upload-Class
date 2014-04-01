<?php
/**
 * Created by PhpStorm.
 * User: Milan
 * Date: 4/1/14
 * Time: 3:53 AM
 */

namespace milanpetrovic;


class UploadFile
{

    protected $destination;
    protected $messages = array();


    /**
     * Takes path to upload directory, checks if it's writable and valid.
     * If trailing slash is omitted, adds it.
     *
     * @param $uploadDir String
     * @throws \Exception
     */
    public function __construct($uploadDir)
    {
        if ( !is_dir($uploadDir) || !is_writable($uploadDir) ) {
            throw new \Exception("$uploadDir must be valid writable directory.");
        }

        if ( $uploadDir[strlen($uploadDir) - 1] != "/") {
            $uploadDir .= "/";
        }

        $this->destination = $uploadDir;
    }

    public function upload()
    {
        $uploadedFile = current($_FILES);
        if ( $this->checkFile($uploadedFile) ) {
            $this->moveFile($uploadedFile);
        }
    }

    /**
     * Takes reference to current file in $_FILES array as argument...
     *
     * @param $file Array
     * @return bool
     */
    protected function checkFile($file)
    {
        if ( $file["error"] != 0 ) {
            $this->getErrorMessage($file);
            return false;
        }
        return true;
    }

    protected function getErrorMessage($file)
    {
        switch( $file["error"] ) {
            case 1:
            case 2:
                $this->messages[] = $file["name"] . " is too big to upload.";
                break;
            case 3:
                $this->messages[] = $file["name"] . " was only partially uploaded.";
                break;
            case 4:
                $this->messages[] = "No file selected";
                break;
            default:
                $this->messages[] = "There was a problem uploading " . $file["name"];
                break;
        }
    }

    protected function moveFile($file)
    {
        echo $file['name'] . " was uploaded successfully";
    }

} 