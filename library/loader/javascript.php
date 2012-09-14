<?php

namespace Slrfw\Library\Loader;

/** @todo faire la présentation du code */

class Javascript {

    private $libraries;

    public function __construct() {
        $this->libraries = array();
    }

    public function getLibraries() {
        return $this->libraries;
    }

    public function loadedLibraries() {
        return array_keys($this->libraries);
    }

    public function  __toString()
    {
        $js = "";
        foreach ($this->libraries as $lib)
            $js .= '<script src="' . $lib["src"] . '" type="text/javascript"></script>' . "\n\t";
        return $js;
    }


    public function addLibrary($path, $local = true)
    {
//        if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest')
            $this->libraries[]["src"] = (substr($path, 0, 7) == 'http://' || substr($path, 0, 8) == 'https://' ? '' : 'js/') . $path;
    }

}

