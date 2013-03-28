<?php
namespace SPC\Svn;
/**
 * 
 * @author Shaked
 *
 */
class Parser_Content
{
    /**
     * @var string
     */
    private $filename;
    /**
     * @var array
     */
    private $info = array();  
    /**
     * @var array
     */
    private $revisions;
    /**
     * @var string
     */
    private $filepath = ''; 
    /**
     * @var array
     */
    private $headContent;
    
    const NEW_FILE = -1; 

    public function __construct ($filename)
    { 
        $lastSlash = strrpos($filename, '/'); 
        if (false !== $lastSlash){
            $lastSlash += 1; 
            $this->filepath  = substr($filename,0,$lastSlash);
        }
        $this->filename  = substr($filename,$lastSlash); 
    }
    /**
     * @param int $revision
     */
    public function addRevision($revision){
        $this->revisions[] = (int)$revision;
    }
    
    /**
     * @return boolean
     */
    public function isAdded(){
        return $this->revisions[1] == 0;
    }
    
    /**
     * @return boolean
     */
    public function isRemoved(){
        return $this->revisions[0] == 0;
    }
    
    /**
     * @param int $method
     * @param string $content
     */
    public function add($method,$content){
        $this->info[] = new Parser_Content_Info($method,$content); 
    } 
    
    /**
     * @param int $type
     * @return number
     */
    public function getCountByType($type){
        $count = 0; 
        foreach ($this->info as $cinfo){
            /* @var $cinfo Parser_Content_Info */
            if ($cinfo->getMethod() == $type){
                $count++;
            }
        }
        return $count;
    } 
    

    /**
     * @return array
     */
    public function getHeadContent(){
        return $this->headContent;
    }
    
    /**
     * @return array
     */
    public function setHeadContent($row1,$row2,$row3){
        return $this->headContent = array($row1,$row2,$row3);
    }
    

    /**
     *
     * @return the $info
     */
    public function getInfo ()
    {
        return $this->info;
    }
    
    /**
     *
     * @return the $filepath
     */
    public function getFilepath ()
    {
        return $this->filepath;
    }
    
    /**
     * @return string
     */
    public function getFilename(){
        return $this->filename; 
    }
}