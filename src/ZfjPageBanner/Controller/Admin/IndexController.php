<?php
namespace ZfjPageBanner\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventInterface;
use ZfjPageBanner\Exception\InvalidOptionException;
use ZfjPageBanner\Exception\ProfilerException;
use Nette\Diagnostics\Debugger;
use ZfjPageBanner\Entity\Navigation;
use ZfjPageBanner\Collector\AbstractEntityCollector;
use ZfjPageBanner\Collector\AbstractCollector;
use ZfjPageBanner\Entity\PageBanner;
use Zend\View\Model\JsonModel;
use ZfjPageBanner\Entity\Images;
use Zend\Uri\Http;
use Zend\Http\Request;
use ZfjPageBanner\Options;

class Admin_IndexController extends AbstractActionController
{

	/**
	 * Zend\Mvc\MvcEvent::EVENT_BOOTSTRAP event callback
	 *
	 * @param EventInterface $event        	
	 * @throws Exception\InvalidOptionException
	 * @throws Exception\ProfilerException
	 */
	public function onBootstrap (EventInterface $event)
	{
		if (PHP_SAPI === 'cli') {
			return;
		}
		
		$app = $event->getApplication();
		$em = $app->getEventManager();
		$sem = $em->getSharedManager();
		$sm = $app->getServiceManager();
		
		$options = $sm->get('ZfjPageBanner\Config');
		
		if (! $options->isEnabled()) {
			return;
		}
		
		$report = $sm->get('ZfjPageBanner\Report');
		
		if ($options->canFlushEarly()) {
			$em->attachAggregate($sm->get('ZfjPageBanner\FlushListener'));
		}
		
		if ($options->isStrict() && $report->hasErrors()) {
			throw new InvalidOptionException(implode(' ', $report->getErrors()));
		}
		
		$em->attachAggregate($sm->get('ZfjPageBanner\ProfilerListener'));
		
		if ($options->isToolbarEnabled()) {
			$sem->attach('profiler', $sm->get('ZfjPageBanner\ToolbarListener'), 
					null);
		}
		
		if ($options->isStrict() && $report->hasErrors()) {
			throw new ProfilerException(implode(' ', $report->getErrors()));
		}
	}

	public function indexAction ()
	{
		$this->onBootstrap($this->getEvent());
		
		$repo = $this->getEntityManager()->getRepository(
				'ZfjPageBanner\Entity\PageBanner');
		
		$navigations = $repo->findBy(array(
			'parent' => null
		));
		
		$options = $this->getServiceLocator()->get('ZfjPageBanner\Config');
		$collectors = $options->getCollectors();
		$sm = $this->getServiceLocator();
		$uploader = $sm->get('ZfJoacubUploaderTwb')->create('zfj_banner_page_uploader');
		
		return new ViewModel(
				array(
					'navigations' 	=> $navigations,
					'activeMenu' 	=> $this->params()->fromQuery('menu', null),
					'repo' 			=> $repo,
					'em' 			=> $this->getEntityManager(),
					'collectors' 	=> $collectors,
					'sm' 			=> $sm,
					'uploader'		=> $uploader
				));
	}

	public function createAction ()
	{
		$em = $this->getEntityManager();
		$menuName = $this->params()->fromPost('menu-name', null);
		
		if ($menuName === null)
			return $this->redirect()->toRoute('zfcadmin/ZfjPageBanner');
		
		$entity = new PageBanner();
		
		$entity->setTitle($menuName);
		
		$em->persist($entity);
		$em->flush();
		
		return $this->redirect()->toRoute('zfcadmin/ZfjPageBanner', array(), 
				array(
					'query' => array(
						'menu' => $entity->getId()
					)
				));
	}

	public function updateAction ()
	{
		$em = $this->getEntityManager();
		$menuId = $this->params()->fromPost('menu', null);
		$menuName = $this->params()->fromPost('menu-name', null);
		$menu_item_db_id = $this->params()->fromPost('menu-item-db-id', null);
		
		$post = $this->params()->fromPost();
		
		if ($menuName === null || $menuId === null)
			return $this->redirect()->toRoute('zfcadmin/ZfjPageBanner');
		
		$repo = $em->getRepository('ZfjPageBanner\Entity\PageBanner');
		
		$menuEntity = $em->find('ZfjPageBanner\Entity\PageBanner', $menuId);
		
		$_children = $repo->getChildren($menuEntity);
		
		$children = array();
		foreach ($_children as $child) {
			$children[$child->getId()] = $child;
		}
		
		unset($_children);
		
		$menuEntity->setTitle($menuName);
		
		$em->persist($menuEntity);
		
		$postFields = array(
			'menu-item-db-id',
			'menu-item-object-id',
			'menu-item-object',
			'menu-item-parent-id',
			'menu-item-position',
			'menu-item-collector',
			'menu-item-title',
			'menu-item-url',
			'menu-item-description',
			'menu-item-attr-title',
			'menu-item-target',
			'menu-item-classes',
			'menu-item-xfn'
		);
		
		$downCount = 0;
		foreach ((array) $menu_item_db_id as $_key => $k) {
		    $downCount++;
			// Menu item title can't be blank
			if (empty($post['menu-item-title'][$_key]))
				continue;
			
			$args = array();
			foreach ($postFields as $field)
				$args[$field] = isset($post[$field][$_key]) ? $post[$field][$_key] : '';
			
			$item = $children[$args['menu-item-db-id']];
			$item instanceof PageBanner;
			
			$item->setTitle($args['menu-item-title']);
			$item->setTitleAttribute($args['menu-item-attr-title']);
			$item->setCss($args['menu-item-classes']);
			$item->setDescription($args['menu-item-description']);
			$item->setTarget((bool) $args['menu-item-target']);
			$item->setUrl($args['menu-item-url']);
			
			$parent = $em->find('ZfjPageBanner\Entity\Navigation', 
					$args['menu-item-parent-id']);
			$item->setParent($parent);
			
			$em->persist($item);
			$em->flush($item);
			
			$repo->moveDown($item, true);
			
			unset($children[$args['menu-item-db-id']]);
			
			$first = false;
		}
		
		$em->flush();
		
		foreach ($children as $child) {
			$repo->removeFromTree($child);
			$em->clear(); // clear cached nodes
		}
		
		
		return $this->redirect()->toRoute('zfcadmin/ZfjPageBanner', array(), 
				array(
					'query' => array(
						'menu' => $menuEntity->getId()
					)
				));
	}

	public function addMenuItemAction ()
	{
		$em = $this->getEntityManager();
		$items = $this->params()->fromPost('menu-item', null);
		$menu = $this->params()->fromPost('menu', null);
		
		$menuEntity = $em->find('ZfjPageBanner\Entity\PageBanner', $menu);
		
		$options = $this->getServiceLocator()->get('ZfjPageBanner\Config');
		
		$collectors = $options->getCollectors();
		
		$router = $this->getServiceLocator()->get('router');
		
		try {
			foreach ($items as &$item) {
				$collector = $this->getServiceLocator()->get(
						$collectors[$item['collector']]);
				$entity = null;
				switch (true) {
					case $collector instanceof AbstractEntityCollector:
						$entity = $em->find($collector->getEntity(), $item['id']);
						
						$entityNavigation = new PageBanner();
						$entityNavigation->setTitle($collector->getTitle($entity));
						$entityNavigation->setCollector($collector->getName());
						$entityNavigation->setParent($menuEntity);
						$entityNavigation->setReferenceId($item['id']);
						break;
					case $collector instanceof AbstractCollector:
						$entityNavigation = new PageBanner();
						$entityNavigation->setTitle($item['menu-item-title']);
						$entityNavigation->setCollector($collector->getName());
						$entityNavigation->setParent($menuEntity);
						
						$uri = new Http($item['menu-item-url']);
						$request = new Request();
						$request->setUri($uri);
						$match = $router->match($request);
						
						$options = $this->getServiceLocator()->get('ZfjPageBanner\Config');
						$options instanceof Options;
						
						$optionsRouter = $options->getRouter($match->getMatchedRouteName());
						
						$repo = $em->getRepository($optionsRouter['entity']);
						$entity = $repo->findOneBy(array($optionsRouter['identifier-db'] => $match->getParam($optionsRouter['identifier-param'])));
						$entityNavigation->setReferenceId($entity->getId());
						$entityNavigation->setUrl($item['menu-item-url']);
						break;
				}
				
				$em->persist($entityNavigation);
				$em->flush($entityNavigation);
				
				$item['data'] = $entityNavigation;
				$item['entity'] = $entity;
				$item['collector'] = $collector;
			}
		} catch (\Exception $e) {
			
			echo $e->getMessage();
			exit();
		}
		
		$viewModel = new ViewModel(array(
			'menuItems' => $items
		));
		$viewModel->setTerminal(true);
		return $viewModel;
	}
	
	public function saveImageAction() 
	{
		$image = $this->params()->fromQuery('imageid');
		$page = $this->params()->fromQuery('pageid');
		$page = $this->getEntityManager()->find('ZfjPageBanner\Entity\PageBanner', $page);
		$image = $this->getEntityManager()->find('FileBank\Entity\File', $image);
		
		$entity = new Images();
		$entity->setImage($image);
		$entity->setPage($page);
		
		$this->getEntityManager()->persist($entity);
		$this->getEntityManager()->flush($entity);
		
		return new JsonModel(array('image' => $entity->getId()));
	}
	
	public function removeImageAction()
	{
		$image = $this->params()->fromQuery('imageid');
		$image = $this->getEntityManager()->find('ZfjPageBanner\Entity\Images', $image);
		
		if($image) {
			$this->getEntityManager()->remove($image);
			$this->getEntityManager()->flush($image);
		}
	
		return new JsonModel(array('result' => true));
	}
	
	public function typeImageAction()
	{
		$image = $this->params()->fromQuery('imageid');
		$image = $this->getEntityManager()->find('ZfjPageBanner\Entity\Images', $image);
		
		$image->setType($this->params()->fromQuery('type'));
	
		if($image) {
			$this->getEntityManager()->persist($image);
			$this->getEntityManager()->flush($image);
		}
	
		return new JsonModel(array('result' => true));
	}

	/**
	 *
	 * @return \Doctrine\ORM\EntityManager
	 */
	protected function getEntityManager ()
	{
		return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
	}
}