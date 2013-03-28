<?php
namespace SPC\Svn;
use Webcreate\Vcs\Common\Commit;
/**
 * 
 * @author Shaked
 *
 */
class Parser { 
    /**
     * @var string
     */
    private $content = ''; 
    /**
     * @var array
     */
    private $files = array();
    /**
     * @var array
     */ 
    private $lines = array(); 
    /**
     * @var array
     */
    private $parsedContent = array(); 
    /**
     * @var string
     */
    private $path = '';
    
    const STATUS_ADDED         = '+';
    const STATUS_MODIFIED      = 'M'; 
    const STATUS_REMOVED       = '-';
    const STATUS_EQUAL         = ' ';
    const STATUS_TEXT_ADDED    = 1;
    const STATUS_TEXT_REMOVED  = -1;
    const STATUS_TEXT_EQUAL    = 0;
    const TODO                 = 'todo';
     
   
    /**
     * @return the $files
     */
    public function getFiles ()
    {
        return $this->files;
    }

	/**
     * @return the $lines
     */
    public function getLines ()
    {
        return $this->lines;
    }

	/**
     * @return the $parsedContent
     */
    public function getParsedContent ()
    {
        return $this->parsedContent;
    }

	/**
     * @return the $content
     */
    public function getContent ()
    {
        return $this->content;
    }
    
    /**
     * 
     * @param string $action
     * @return boolean
     */
    private function isModified($action){
       return self::STATUS_MODIFIED == $action; 
    }
    
    /**
     * 
     * @param array $paths
     * @return array
     */
    private function getModified(array $paths){
        $modified = array();  
        foreach($paths as $path){
            if ($this->isModified($path['action'])){ 
                $modified[] = $path['path']; 
            }
        }
        return $modified; 
    }
    
    /**
     * @return string
     */
    public function getPath(){
        return $this->path;
    }
     
    
    /**
     * 
     * @param string $content
     * @param Commit $head
     * @param string $path
     */
    public function run($content, Commit $head,$path){
        $this->content  = $content; 
        $this->path     = $path;    
         
        $rows           = explode(PHP_EOL, $this->content);  
        foreach ($rows as $key=>$row) {
            preg_match("#Index:\s(.*)#", $row,$matches);
            if (!empty($matches[1])) {
                $filename = trim($matches[1]); 
                if (!isset($this->files[$filename])){ 
                    $this->files[$filename] = $filename;
                    $parsedContent[$filename] = new Parser_Content($filename); 
                } 
            } elseif (preg_match("#\=+#",$row,$matches)){
                 $parsedContent[$filename]->setHeadContent(
                     $rows[$row+2],
                     $rows[$row+3],
                     $rows[$row+4]
                 );
            } elseif(preg_match("#revision\s(\d+)#", $row,$matches)) {
                $parsedContent[$filename]->addRevision($matches[1]);
            } elseif (false !== stripos($row, self::TODO)){
                $this->addTodo($row,$filename);
            } else { 
                try { 
                    $method = $this->getRowStatus($row);
                } catch (Parser_Exception $e){
                    continue; 
                } 
                $tempContent = substr($row,1); 
                $parsedContent[$filename]->add($method,$tempContent);  
            }
        }    
        $this->parsedContent = $parsedContent; 
    }
    
    /**
     * @param strnig $todo
     * @param string $fullpath
     */
    private function addTodo($todo,$fullpath){
        if (!isset($this->todo[$fullpath])){
            $this->todo[$fullpath] = array(); 
        }
        $this->todo[$fullpath][] = $todo;
    }
    
    /**
     * @return multitype:
     */
    public function getTodo(){
        return $this->todo;
    }
    
    /**
     * 
     * @param string $row
     * @throws Parser_Exception
     * @return string
     */
    private function getRowStatus($row){
        $chars = str_split($row);
        if (isset($chars[1]) && $chars[0] == $chars[1]){
            throw new Parser_Exception('Row is part of header data');
        }
        switch($chars[0]){
            case self::STATUS_ADDED:
                    return self::STATUS_TEXT_ADDED; 
                break;
            case self::STATUS_REMOVED:
                    return self::STATUS_TEXT_REMOVED; 
                break;
            case self::STATUS_EQUAL: 
                    return self::STATUS_TEXT_EQUAL;
                break;
            default:
                throw new Parser_Exception('Char ' . $chars[0] . ' is not recognized by the system'); 
        }
    }
}

class Parser_Exception extends \Exception{};