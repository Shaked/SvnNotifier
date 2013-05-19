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
use Webcreate\Vcs\Svn\Parser\CliParser;
use Webcreate\Util\Cli;
use Webcreate\Vcs\Common\Adapter\CliAdapter;
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
    
    const PARAM_REPO_NAME  = "%repoName";
    const SUCCCESS_MESSAGE = "Email was sent successfully";  
    const SVN_LOG_LIMIT    = 2;

    public function __construct(Application $app,$repoName){
        $this->repoName = $repoName; 
        $this->svn = new Svn($app['svn']['repos'][$this->repoName]['url'],new CliAdapter($app['svn']['repos'][$this->repoName]['bin'], new Cli(), new CliParser()));
        $this->svn->setCredentials($app['svn']['repos'][$this->repoName]['username'], $app['svn']['repos'][$this->repoName]['password']);
    }   

    /**
     * 
     * @param Request $request
     * @param Application $app
     * @param array $mailTo
     * @param int $revision
     * @throws ControllerException
     * @return boolean
     */
    public function mailDiff (Request $request, Application $app, array $mailTo, $revision)
    {  
        $path          = new Reference($app['svn']['repos'][$this->repoName]['path']); 
        $log           = $this->svn->log($path,$revision,self::SVN_LOG_LIMIT);
 
        /* @var $head Commit */
        $head          = array_shift($log);
        /* @var $prev Commit */
        $prev          = array_shift($log);   
        if (!$prev){
            $prev = $head;
        }

        $diff          = $this->svn->diff($path,$path,$head->getRevision(), $prev->getRevision(),false);  
        $parser        = new Parser();
        $parser->run($diff, $head, $path); 
        $subject       = $this->parseSubject($app, $head);
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

        $this->sendMail($app,$mailTo,$subject,$content);
        return self::SUCCCESS_MESSAGE; 
 
    }
    
    /**
     * @param Application $app
     * @param unknown $mailTo
     * @param unknown $subject
     * @param unknown $content
     * @throws ControllerException
     * @return boolean
     */
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
    private function parseSubject(Application $app,Commit $head){
        $repoMapName   = $this->getRepoMapName($app['svn']['repoMap'],$this->repoName); 
        $subject       = str_replace(self::PARAM_REPO_NAME, $repoMapName, $app['diff']['mail']['subject']);
        $subjectParams = $app['diff']['mail']['params'];
        
        foreach($subjectParams as $param){
            $method = 'get' . ucfirst($param);
            $subject = str_replace('%' . $param, $head->$method(), $subject); 
        }
        
        
        return $subject; 
    }
    
    /**
     * @param array $repoMap
     * @param string $repoName
     * @return string
     */
    private function getRepoMapName(array $repoMap,$repoName){
        return isset($repoMap[$this->repoName])? $repoMap[$this->repoName]:$repoName;
    }
}

class ControllerException extends \Exception{}; 