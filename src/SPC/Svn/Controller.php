<?php
namespace SPC\Svn;

use Webcreate\Vcs\Svn; 
use Webcreate\Vcs\Common\Commit;
use Webcreate\Vcs\Common\VcsFileInfo;
use SPC\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\App_Svn_Parser;
use SPC\Swift\Mailer;
use SPC\Swift\Message;
use Webcreate\Vcs\Common\Reference;
/**
 * 
 * @author Shaked
 *
 */
class Controller
{
    /**
     * @var string
     */
    private $repoName;
    /**
     * @var Svn
     */
    private $svn; 
    
    const PARAM_REPO_NAME = "%repoName"; 
     
    public function __construct(Application $app,$repoName){
        $this->repoName = $repoName; 
        $this->svn = new Svn($app['svn'][$this->repoName]['url']);
        $this->svn->setCredentials($app['svn'][$this->repoName]['username'], $app['svn'][$this->repoName]['password']);
    }   

    /**
     * 
     * @param Request $request
     * @param Application $app
     * @param array $mailTo
     * @throws ControllerException
     * @return boolean
     */
    public function mailDiff (Request $request, Application $app, array $mailTo)
    {  
        $path          = new Reference($app['svn'][$this->repoName]['path']); 
        $log           = $this->svn->log($path);
        /* @var $head Commit */
        $head          = array_shift($log);
        /* @var $prev Commit */
        $prev          = array_shift($log);  
         
        $diff          = $this->svn->diff($path,$path,$head->getRevision(), $prev->getRevision(),false);  
        $parser        = new Parser();
        $parser->run($diff, $head, $path); 
        $subject       = $this->parseSubject($app['diff']['mail'], $head);
        $content       = $app->getTwig()->render('default.twig', array(
            'path'             => $path,  
            'parsedContent'    => $parser->getParsedContent(),
            'todo'             => $parser->getTodo(),
            'svnCommitMessage' => $head->getMessage(),
            'svnHeadInfo'      => $head, 
            'svnPrevInfo'      => $prev, 
            'repoName'         => $this->repoName, 
            'originalContent'  => $parser->getContent(), 
            'svnWebConfig'     => $app['svn']['web'],
        ));
        return $this->sendMail($app,$mailTo,$subject,$content);   
    }
    
    private function sendMail(Application $app,$mailTo,$subject,$content){
        $message = Message::newInstance($app['swiftmailer']['override'])->setFrom($app['swiftmailer']['name'])
            ->setTo($mailTo)
            ->setSubject($subject)
            ->setBody($content,'text/html');
        $failures = array();
        $res = $app->getMailer()->send($message, $failures);
        if ($res > 0) {
            return true;
        } else {
            $failuresStr = implode(',', $failures);
            throw new ControllerException('Failed to send email to: ' . $failuresStr);
        }
    }
    
    /**
     * @param array $mailConfig
     * @param array $head
     * @throws ControllerException
     * @return mixed
     */
    private function parseSubject(array $mailConfig,Commit $head){
        $subject       = str_replace(self::PARAM_REPO_NAME, $this->repoName, $mailConfig['subject']);
        $subjectParams = $mailConfig['params'];
        
        foreach($subjectParams as $param){
            $method = 'get' . ucfirst($param);
            $subject = str_replace('%' . $param, $head->$method(), $subject); 
        }
        
        
        return $subject; 
    }
}

class ControllerException extends \Exception{}; 