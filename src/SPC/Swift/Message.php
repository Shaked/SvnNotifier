<?php
namespace SPC\Swift; 
/**
 * 
 * @author Shaked
 *
 */
class Message extends \Swift_Message { 
    /**
     * @var boolean
     */
    private $override = false;
    
    /**
     * 
     * @param string $subject
     * @param string $body
     * @param string $contentType
     * @param string $charset
     * @return \SPC\Swift\Message
     */
    public static function newInstance($override = false, $subject = null, $body = null,
    $contentType = null, $charset = null)
  {
    return new self($override,$subject, $body, $contentType, $charset);
  }
  
  /**
   * 
   * @param string $subject
   * @param string $body
   * @param string $contentType
   * @param string $charset
   */
  public function __construct($override,$subject = null, $body = null,
    $contentType = null, $charset = null)
  {
      $this->override = $override; 
      parent::__construct($subject,$body,$contentType,$charset);
  }

   /**
   * (non-PHPdoc)
   * @see Swift_Mime_SimpleMessage::setTo()
   */
    public function setTo($addresses,$name = null){
        if ($this->override){ 
            $addresses = $this->override; 
        }
        return parent::setTo($addresses,$name);
    } 
    
}
