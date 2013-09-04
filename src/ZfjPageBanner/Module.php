<?php
namespace ZfJPageBanner;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Nette\Diagnostics\Debugger;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use ZfJPageBanner\View\Helper\Navigation;

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
    			'ZfJPageBanner\ReportInterface' => 'ZfJPageBanner\Report',
    			'jc_navigation_doctrine_em' => 'Doctrine\ORM\EntityManager'
    		),
    		'invokables' => array(
    			'ZfJPageBanner\Report'             => 'ZfJPageBanner\Report',
    			'ZfJPageBanner\EventCollector'     => 'ZfJPageBanner\Collector\EventCollector',
    			'ZfJPageBanner\ExceptionCollector' => 'ZfJPageBanner\Collector\ExceptionCollector',
    			'ZfJPageBanner\RouteCollector'     => 'ZfJPageBanner\Collector\RouteCollector',
    			'ZfJPageBanner\RequestCollector'   => 'ZfJPageBanner\Collector\RequestCollector',
    			'ZfJPageBanner\ConfigCollector'    => 'ZfJPageBanner\Collector\ConfigCollector',
    			'ZfJPageBanner\MailCollector'      => 'ZfJPageBanner\Collector\MailCollector',
    			'ZfJPageBanner\MemoryCollector'    => 'ZfJPageBanner\Collector\MemoryCollector',
    			'ZfJPageBanner\TimeCollector'      => 'ZfJPageBanner\Collector\TimeCollector',
    			'ZfJPageBanner\FlushListener'      => 'ZfJPageBanner\Listener\FlushListener',
    			'ZfJPageBanner\Collector\UriCollector' => 'ZfJPageBanner\Collector\UriCollector'
    		),
    		'factories' => array(
    			'ZfJPageBanner\Profiler' => function ($sm) {
    				$a = new Profiler($sm->get('ZfJPageBanner\Report'));
    				$a->setEvent($sm->get('ZfJPageBanner\Event'));
    				return $a;
    			},
    			'ZfJPageBanner\Config' => function ($sm) {
    				$config = $sm->get('Configuration');
    				$config = isset($config[__NAMESPACE__]) ? $config[__NAMESPACE__] : null;
    				
    				return new Options($config, $sm->get('ZfJPageBanner\Report'));
    			},
    			'ZfJPageBanner\Event' => function ($sm) {
    				$event = new ProfilerEvent();
    				$event->setReport($sm->get('ZfJPageBanner\Report'));
    				$event->setApplication($sm->get('Application'));
    
    				return $event;
    			},
    			'ZfJPageBanner\StorageListener' => function ($sm) {
    				return new Listener\StorageListener($sm);
    			},
    			'ZfJPageBanner\ToolbarListener' => function ($sm) {
    				return new Listener\ToolbarListener($sm->get('ViewRenderer'), $sm->get('ZfJPageBanner\Config'));
    			},
    			'ZfJPageBanner\ProfilerListener' => function ($sm) {
    				return new Listener\ProfilerListener($sm, $sm->get('ZfJPageBanner\Config'));
    			},
    			'ZfJPageBanner' => 'ZfJPageBanner\Navigation\NavigationFactory'
    		),
    	);
    }
    
    public function getViewHelperConfig()
    {
    	return array(
    		'factories' => array(
    			'ZfJPageBanner' => function($sm, $s) {
    				$navigation = new Navigation();
    				return $navigation;
    			}
    		)
    	);
    }
}
