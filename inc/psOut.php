<?php

class psOut extends StdClass {
    static $progress = false;
    static $log = false;
    static $oformat = "cli";
    static $base64 = false;
    
    public function write($data) {
        switch (self::$oformat) {
            case "cli": self::cli($data);
                break;
            case "csv": self::csv($data);
                break;
            case "env": self::env($data);
                break;
            case "php": self::php($data);
                break;
            default: self::error("Unknown output format " . self::$oformat);
        }
    }
    
    public function error($txt, $code = 1) {
        fputs(self::$log, $txt . "\n");
        exit($code);
    }

    public function msg($txt) {
        fputs(self::$log, $txt);
    }
    
    public function progress($num=false,$cnt=false) {
        if (self::$progress) {
            psOut::msg(sprintf("Progress: %d of %d objects...    \r",$num,$cnt));
            flush();
        }
    }
    
    public function nl2slashes($str) {
        return(preg_replace("/\r/m", '\r',preg_replace("/\n/m", '\n',$str)));
    }
    
    public function ifbase64($var) {
        if (!self::$base64) {
            return($var);
        } else {
            return(base64_encode($var));
        }
    }
    
    public function expvar($var) {
        if (is_object($var)) {
            if (isset($var->language) && array_key_exists(psCli::$lang,$var->language)) {
                return(self::ifbase64($var->language[psCli::$lang]));
            } elseif (isset($var->language) && !array_key_exists(psCli::$lang,$var->language)) {
                return(null);
            } else {
                //return(print_r($var,true));
                return(null);
            }
        } elseif (is_array($var)) {
            return(self::ifbase64(print_r($var,true)));
        } else {
            return(self::ifbase64($var));
        }
    }

    public function cli($data) {
        $row = 0;
        reset($data);
        foreach ($data as $line) {
            foreach ($line as $column) {
                echo self::expvar($column)." ";
            }
            echo "\n";
            $row++;
        }
    }

    public function csv($data) {
        $row = 0;
        reset($data);
        foreach ($data as $line) {
            if ($row == 0) {
                foreach ($line as $column => $value) {
                    echo "\"$column\";";
                }
                reset($line);
                echo "\n";
            }
            foreach ($line as $column => $value) {
                echo self::nl2slashes('"'.self::expvar($value).'";');
            }
            echo "\n";
            $row++;
        }
    }

    public function env($data) {
        $row = 0;
        reset($data);
        foreach ($data as $line) {
            foreach ($line as $column => $value) {
                echo sprintf('%s="%s"; ',$column,addslashes(self::expvar($value)));
            }
            echo "\n";
            $row++;
        }
    }
    
    public function php($data) {
        print_r($data);
    }

}
