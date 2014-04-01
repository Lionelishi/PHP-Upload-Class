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
    protected $allowedTypes = array(
        "image/jpeg",
        "image/pjpeg",
        "image/gif",
        "image/png",
        "application/pdf"
    );
    protected $newName;


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
     * Method for changing max file size. If the new size exceed server limit
     * throws new Exception
     *
     * @param $bytes Integer
     * @throws \Exception
     */
    public function setMaxFileSize($bytes)
    {
        $serverMaxSize = self::convertToBytes( ini_get("upload_max_filesize") );
        if ( $bytes > $serverMaxSize ) {
            throw new \Exception("Maximum size cannot exceed server limit for individual files: " . self::convertFromBytes($serverMaxSize));
        }

        if ( is_numeric($bytes) && $bytes > 0 ) {
            $this->maxSize = $bytes;
        }
    }


    /**
     * Converts max file size configured on server to bytes
     *
     * @param $value Mixed file size server limit
     * @return int size in bytes
     */
    public static function convertToBytes($value)
    {
        $value = trim($value);
        $lastChar = strtolower($value[strlen($value) - 1]);
        if ( in_array($lastChar, array("g", "m", "k")) ) {
            // Fall-through switch
            switch ($lastChar) {
                case "g":
                    $value *= 1024;
                case "m":
                    $value *= 1024;
                case "k":
                    $value *= 1024;
            }
        }

        return $value;
    }


    /**
     * Converts bytes into KB and MB
     *
     * @param $bytes integer
     * @return string
     */
    public static function convertFromBytes($bytes)
    {
        $bytes /= 1024;
        if ( $bytes >= 1024 ) {
            return number_format($bytes / 1024, 1) . "MB";
        } else {
            return number_format($bytes, 1) . "KB";
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

        if ( !$this->checkFileType($file) ) {
            return false;
        }

        $this->clearName($file);

        return true;
    }


    /**
     * Takes reference to current file in $_FILES superglobal array
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
                $this->messages[] = $file["name"] . " is too large (max: " . self::convertFromBytes($this->maxSize) . ")";
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
     * Takes reference to current file in $_FILES superglobal array as argument
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


    /**
     * Checks if current file type is in array of allowed types and updates messages array
     * if it's not.
     *
     * @param $file Array
     * @return bool
     */
    protected function checkFileType($file)
    {
        if ( in_array($file["type"], $this->allowedTypes) ) {
            return true;
        } else {
            $this->messages[] = $file["name"] . " is not permitted file type.";
            return false;
        }
    }


    /**
     * Method for replacing white spaces in file name with underscores
     *
     * @param $file Array
     */
    protected function clearName($file)
    {
        $this->newName = null;
        $noSpaces = str_replace(" ", "_", $file["name"]);
        if ( $noSpaces != $file["name"] ) {
            $this->newName = $noSpaces;
        }
    }


    protected function moveFile($file)
    {
        $message = $file['name'] . " was uploaded successfully";
        if ( !is_null($this->newName) ) {
            $message .= ", and was renamed as " . $this->newName;
        }
        $message .= ".";

        $this->messages[] = $message;
    }

} 