<?php
namespace SPC\Svn;

class Parser_Content_Info { 
    /**
     * @var string
     */
    private $method;    
    /**
     * @var string
     */
    private $content; 
    /**
     * @return the $method
     */
    public function getMethod ()
    {
        return $this->method;
    }

	/**
     * @return the $content
     */
    public function getContent ()
    {
        return $this->content;
    }

	public function __construct($method,$content){ 
        $this->method  = $method; 
        $this->content = $content; 
    }
}