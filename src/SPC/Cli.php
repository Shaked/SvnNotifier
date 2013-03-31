<?php
namespace SPC;
use Symfony\Component\HttpFoundation\Request;
/**
 * 
 * @author Shaked
 *
 */
class Cli { 
    const REQUEST_METHOD_GET = 'GET';   
    /**
     * 
     * @param array $options
     * @throws CliException
     * @return Ambigous <\Symfony\Component\HttpFoundation\Request, \Symfony\Component\HttpFoundation\Request>
     */
    public static function createRequest(array $options = null){
        //Browser support
        if (!isset($_SERVER['argv'])){ 
            return null;
        }
        
        if (!isset($options)){
            throw new CliException(); 
        }
        
        if (!isset($options['r'])){
            throw new CliException('Missing repository name (-r)'); 
        }
        if (!isset($options['e'])){
            throw new CliException('Missing recipients (-e)'); 
        }
        
        $path = '/' . $options['r'] . '/' . $options['e'];
        return Request::create($path, self::REQUEST_METHOD_GET);
    }
}

class CliException extends \Exception{
    
    public function __construct($message=null,$code=null,$previous=null){
        parent::__construct($message,$code,$previous); 
        $this->message .= PHP_EOL . self::help();
    }
    /**
     * 
     * @return string
     */
    public static function help(){
        return <<<EOF
        
----------------- ### HELP ### ----------------- 
Parameter Name    |    Description
-r                | Repository name 
-e                | Recipientes - email1,email2 (Comma separated) 

Example: 
    php ./index.php -r RepoName -e email1@domain.com,email2@newdomain.com

EOF;
    }
}