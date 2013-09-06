<?php

namespace ZfjPageBanner\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceManager;
use Doctrine\ORM\EntityManager;
use Zend\Uri\Http;
use Zend\Http\Request;
use Nette\Diagnostics\Debugger;

class PageBanner extends AbstractHelper
{
	/**
	 * 
	 * @var EntityManager
	 */
	protected $em;
	/**
	 * 
	 * @var ServiceManager
	 */
	protected $sm;
	
	public function __construct($sm)
	{
		$this->sm = $sm->getServiceLocator();
		$this->em = $this->sm->get('Doctrine\ORM\EntityManager');
	}
	/**
	 * (non-PHPdoc)
	 * @see \Zend\View\Helper\Navigation::__invoke()
	 */
	public function __invoke()
	{
		return $this;
	}
	
	public function getByRoute($route, $params)
	{
		$options = $this->sm->get('ZfjPageBanner\Config');
		$optionsRouter = $options->getRouter($route);
		$repo = $this->em->getRepository($optionsRouter['entity']);
		$pageBannerRepo = $this->em->getRepository('ZfjPageBanner\Entity\PageBanner');
		
		$result = $repo->findOneBy(array($optionsRouter['identifier-db'] => $params[$optionsRouter['identifier-param']]));
		
		return $pageBannerRepo->findOneBy(array('entity' => $optionsRouter['entity'], 'referenceId' => $result->getId()));
	}
	
	public function getByUrl($url, $params)
	{
		$router = $this->sm->get('router');
		$uri = new Http($url);
		$request = new Request();
		$request->setUri($uri);
		$match = $router->match($request);
		return $this->getByRoute($match->getMatchedRouteName(), $params);
	}
    
}