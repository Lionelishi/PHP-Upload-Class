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
} 