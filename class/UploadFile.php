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
    protected $maxSize = 102400; // bytes


    /**
     * Takes path to upload directory, checks if it's writable and valid.
     * Add trailing slash if it's omitted
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

    /**
     * Method for changing max file size allowed
     *
     * @param $bytes Integer
     */
    public function setMaxFileSize($bytes)
    {
        if ( is_numeric($bytes) && $bytes > 0 ) {
            $this->maxSize = $bytes;
        }
    }

    /**
     * Calls method for checking file and if error code is 0 calls move method
     */
    public function upload()
    {
        $uploadedFile = current($_FILES);
        if ( $this->checkFile($uploadedFile) ) {
            $this->moveFile($uploadedFile);
        }
    }

    /**
     * Getter method for messages
     *
     * @return array Array of messages
     */
    public function getMessages() {
        return $this->messages;
    }

    /**
     * Takes reference to current file in $_FILES array as argument
     * Function for checking error code in file array and size of file
     *
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

        if ( !$this->checkFileSize($file) ) {
            return false;
        }
        return true;
    }

    /**
     * Takes reference to current file in $_FILES array as argument
     * Using switch statement adds error messages to messages array
     * for different error level
     *
     * @param $file Array
     */
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

    /**
     * Takes reference to current file in $_FILES array as argument
     * Returns false if file is empty or larger than maximum size, otherwise returns true
     *
     * @param $file Array
     * @return bool
     */
    protected function checkFileSize($file)
    {
        if ( $file["size"] == 0 ) {
            $this->messages[] = $file["name"] . " is empty.";
            return false;
        } elseif ( $file["size"] > $this->maxSize ) {
            $this->messages[] = $file["name"] . " exceeds maximum size allowed for file.";
            return false;
        } else {
            return true;
        }
    }


    protected function moveFile($file)
    {
        $this->messages[] = $file['name'] . " was uploaded successfully";
    }

} 