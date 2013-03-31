<?php 
namespace SPC;

use Symfony\Component\HttpFoundation\Request;
use Igorw\Silex\ConfigServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Symfony\Component\Validator\Constraints as Assert;
use Silex\Provider\ValidatorServiceProvider;
    
class Application extends \Silex\Application {
    /**
     * @var string
     */
	private $rootDir;
	/**
	 * @var string
	 */
	private $env;
	
	const ENV_DEV  = 'dev'; 
	const ENV_PROD = 'prod';
	public function __construct($env,$rootDir) {
		parent::__construct();
		$this->rootDir = $rootDir;
		$this->env     = $env; 
	}

	/**
	 * @return \SPC\Application
	 */
	public function init() {
		$this->initConfig();
	    $this->initProviders();
		$this->initRouting();
		return $this;
	}
	
	public function initConfig(){
	    $configFile = $this->rootDir . "/../config/$this->env.json";
	    $this->register(new ConfigServiceProvider($configFile));
	}

	private function initProviders() {
		$this->initTwig(); 
		$this->register(new ValidatorServiceProvider());
        $this->register(new SwiftmailerServiceProvider(),array('swiftmailer.options' => $this['swiftmailer']));
       
	}
	
	private function initTwig(){
	    $this->register(new \Silex\Provider\TwigServiceProvider(), array(
	        'twig.path' =>$this->rootDir.'/../views'
	    ));
	    
	    $this['twig'] = $this->share($this->extend('twig', function($twig, $app) {
           $twig->addFilter('nl2br', new \Twig_Filter_Function('nl2br', array('is_safe' => array('html'))));
           $twig->addFilter('count', new \Twig_Filter_Function('count', array('is_safe' => array('html'))));
           $twig->addFilter('constant', new \Twig_Filter_Function('constant', array('is_safe' => array('html'))));
           $twig->addFilter('explode', new \Twig_Filter_Function('explode', array('is_safe' => array('html'))));
           $twig->addFilter('implode', new \Twig_Filter_Function('implode', array('is_safe' => array('html'))));
           $twig->addFilter('sha1', new \Twig_Filter_Function('sha1', array('is_safe' => array('html'))));
           return $twig;
        }));
	}

	private function initRouting() {
	    $this->get('/{repoName}/{mailTo}', function(Application $app,$repoName, $mailTo){
	        if (!isset($app['svn']['repos'][$repoName])){
	            throw new ApplicationException("Repository name: $repoName does not exist in config file.");
	        }
	        $request       = $app['request'];
	        $mailTo        = explode(',',$mailTo);
	        $mailTo        = array_filter($mailTo,function($email) use ($app) {
	            $errors = $app['validator']->validateValue($email, new Assert\Email());
	            if (count($errors) > 0){
	                return false; 
	            }
	            return true; 
	        });
	        if (empty($mailTo)){
	            throw new ApplicationException("MailTo parameter is required. please use: email1,email2,email3,....,emailN");
	        }
	        $controller    = new Svn\Controller($app,$repoName);
	        $response      = $controller->mailDiff($request, $app, $mailTo);
	        return $response;
	    });
	    
	    
	    $this->error(function(\Exception $e,$code){
	        var_dump($e->getMessage(),$code);die;
	    });
	}


	/**
	 * @return string
	 */
	public function getRootDir() {
		return $this->rootDir;
	}
	/**
	 * 
	 * @return boolean
	 */
	public function isProdEnv() {
		return $this->env == self::ENV_PROD;
	}

	/**
	 * @return \Swift_Mailer
	 */
	public function getMailer() {
		return $this['mailer'];
	}
	/**
	 *
	 * @return \Symfony\Component\Routing\Generator\UrlGenerator
	 */
	public function getUrlGenerator() {
		return $this['url_generator'];
	}

	/**
	 * @return \Silex\Provider\TwigCoreExtension
	 */
	public function getTwig() {
		return $this['twig'];
	}

	/**
	 * 
	 * @return \Symfony\Component\HttpFoundation\Session\Session
	 */
	public function getSession() {
		return $this['session'];
	}
}

class ApplicationException extends \Exception{};