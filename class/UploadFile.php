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
    protected $maxSize = 51200; // bytes
    protected $allowedTypes = array("image/jpeg", "image/pjpeg", "image/gif", "image/png", "application/pdf");
    protected $newName;
    protected $typeCheckingOn = true;
    protected $riskyTypes = array("bin", "bat", "cgi", "dll", "exe", "js", "pl", "php", "py", "sh");
    protected $defaultSuffix = ".upload";
    protected $renameDuplicates;


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
     * Method for adding default suffix to uploaded files. Suffix is provided
     * as argument with leading dot. If dot is omitted method will add it at the
     * beginning.
     *
     * @param null $suffix string
     */
    public function allowAllTypes($suffix = null)
    {
        $this->typeCheckingOn = false;
        if ( !is_null($suffix) ) {
            if ( strpos($suffix, ".") === 0  || $suffix == "" ) {
                $this->defaultSuffix = $suffix;
            } else {
                $this->defaultSuffix = ".$suffix";
            }
        }
    }

    /**
     * Takes boolean as argument and pass it to the class property. If it's true
     * files overwrite is disabled and vice versa.
     * Calls method for checking file and if error code is 0 calls move method
     *
     * @param bool $renameDuplicates
     */
    public function upload($renameDuplicates = true)
    {
        $this->renameDuplicates = $renameDuplicates;
        $uploadedFile = current($_FILES);

        if ( is_array($uploadedFile["name"]) ) {
            foreach( $uploadedFile["name"] as $key => $value ) {
                $currentFile["name"] = $uploadedFile["name"][$key];
                $currentFile["type"] = $uploadedFile["type"][$key];
                $currentFile["tmp_name"] = $uploadedFile["tmp_name"][$key];
                $currentFile["error"] = $uploadedFile["error"][$key];
                $currentFile["size"] = $uploadedFile["size"][$key];

                if ( $this->checkFile($currentFile) ) {
                    $this->moveFile($currentFile);
                }
            }
        } else {
            if ( $this->checkFile($uploadedFile) ) {
                $this->moveFile($uploadedFile);
            }
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

        if ( $this->typeCheckingOn ) {
            if ( !$this->checkFileType($file) ) {
                return false;
            }
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
     * Method for replacing white spaces in file name with underscores.
     * Method is also responsible for adding default suffix to new file name
     * if typeCheckingOn is turned off to prevent files with risky extensions
     * to be uploaded. Handles renaming duplicated files if renameDuplicates
     * property is true
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

        $nameParts = pathinfo($noSpaces);
        $extension = isset($nameParts["extension"]) ? $nameParts["extension"] : "";
        if ( !$this->typeCheckingOn && !empty($this->defaultSuffix) ) {
            if ( in_array($extension, $this->riskyTypes) || empty($extension) ) {
                $this->newName = $noSpaces . $this->defaultSuffix;
            }
        }

        if ( $this->renameDuplicates ) {
            $name = isset( $this->newName ) ? $this->newName : $file["name"];
            $existingNames = scandir($this->destination);
            if ( in_array($name, $existingNames) ) {
                $i = 1;
                do {
                    $this->newName = $nameParts["filename"] . "_" . $i++;
                    if ( !empty($extension) ) {
                        $this->newName .= ".$extension";
                    }
                    if ( in_array($extension, $this->riskyTypes) ) {
                        $this->newName .= $this->defaultSuffix;
                    }
                } while( in_array($this->newName, $existingNames) );
            }
        }
    }


    /**
     * Method that move uploaded files to destination.
     *
     * @param $file Array
     */
    protected function moveFile($file)
    {
        $fileName = isset($this->newName) ? $this->newName : $file["name"];
        $success = move_uploaded_file($file["tmp_name"], $this->destination . $fileName);
        if ( $success ) {
            $message = $file['name'] . " was uploaded successfully";
            if ( !is_null($this->newName) ) {
                $message .= ", and was renamed as " . $this->newName;
            }
            $message .= ".";

            $this->messages[] = $message;
        } else {
            $this->messages[] = "Unable to upload " . $file["name"];
        }
    }

} 