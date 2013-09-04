<?php
namespace ZfjPageBanner;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Nette\Diagnostics\Debugger;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use ZfjPageBanner\View\Helper\Navigation;

class Module implements
    ConfigProviderInterface,
    AutoloaderProviderInterface,
    ServiceProviderInterface,
    ViewHelperProviderInterface
{
	
	
    /**
     * {@InheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * {@InheritDoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }
    
    /**
     * @inheritdoc
     */
    public function getServiceConfig()
    {
    	return array(
    		'aliases' => array(
    			'ZfjPageBanner\ReportInterface' => 'ZfjPageBanner\Report',
    			'zfj_page_bannerdoctrine_em' => 'Doctrine\ORM\EntityManager'
    		),
    		'invokables' => array(
    			'ZfjPageBanner\Report'             => 'ZfjPageBanner\Report',
    			'ZfjPageBanner\EventCollector'     => 'ZfjPageBanner\Collector\EventCollector',
    			'ZfjPageBanner\ExceptionCollector' => 'ZfjPageBanner\Collector\ExceptionCollector',
    			'ZfjPageBanner\RouteCollector'     => 'ZfjPageBanner\Collector\RouteCollector',
    			'ZfjPageBanner\RequestCollector'   => 'ZfjPageBanner\Collector\RequestCollector',
    			'ZfjPageBanner\ConfigCollector'    => 'ZfjPageBanner\Collector\ConfigCollector',
    			'ZfjPageBanner\MailCollector'      => 'ZfjPageBanner\Collector\MailCollector',
    			'ZfjPageBanner\MemoryCollector'    => 'ZfjPageBanner\Collector\MemoryCollector',
    			'ZfjPageBanner\TimeCollector'      => 'ZfjPageBanner\Collector\TimeCollector',
    			'ZfjPageBanner\FlushListener'      => 'ZfjPageBanner\Listener\FlushListener',
    			'ZfjPageBanner\Collector\UriCollector' => 'ZfjPageBanner\Collector\UriCollector'
    		),
    		'factories' => array(
    			'ZfjPageBanner\Profiler' => function ($sm) {
    				$a = new Profiler($sm->get('ZfjPageBanner\Report'));
    				$a->setEvent($sm->get('ZfjPageBanner\Event'));
    				return $a;
    			},
    			'ZfjPageBanner\Config' => function ($sm) {
    				$config = $sm->get('Configuration');
    				$config = isset($config[__NAMESPACE__]) ? $config[__NAMESPACE__] : null;
    				
    				return new Options($config, $sm->get('ZfjPageBanner\Report'));
    			},
    			'ZfjPageBanner\Event' => function ($sm) {
    				$event = new ProfilerEvent();
    				$event->setReport($sm->get('ZfjPageBanner\Report'));
    				$event->setApplication($sm->get('Application'));
    
    				return $event;
    			},
    			'ZfjPageBanner\StorageListener' => function ($sm) {
    				return new Listener\StorageListener($sm);
    			},
    			'ZfjPageBanner\ToolbarListener' => function ($sm) {
    				return new Listener\ToolbarListener($sm->get('ViewRenderer'), $sm->get('ZfjPageBanner\Config'));
    			},
    			'ZfjPageBanner\ProfilerListener' => function ($sm) {
    				return new Listener\ProfilerListener($sm, $sm->get('ZfjPageBanner\Config'));
    			},
    			'ZfjPageBanner' => 'ZfjPageBanner\Navigation\NavigationFactory'
    		),
    	);
    }
    
    public function getViewHelperConfig()
    {
    	return array(
    		'factories' => array(
    			'ZfjPageBanner' => function($sm, $s) {
    				$navigation = new Navigation();
    				return $navigation;
    			}
    		)
    	);
    }
}
