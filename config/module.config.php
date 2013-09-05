<?php
namespace ZfjPageBanner;
use Zend\Json\Expr;
return array(
	'doctrine' => array(
		'driver' => array(
			__NAMESPACE__ . '_driver' => array(
				'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
				'cache' => 'array',
				'paths' => array(
					__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity'
				)
			),
			'orm_default' => array(
				'drivers' => array(
					__NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
				)
			)
		)
	),
	'asset_manager' => array(
		'resolver_configs' => array(
			'paths' => array(
				__DIR__ . '/../public/'
			)
		)
	),
	'view_manager' => array(
		'template_path_stack' => array(
			__DIR__ . '/../view'
		)
	),
	'router' => array(
		'routes' => array(
			'zfcadmin' => array(
				'child_routes' => array(
					__NAMESPACE__ => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/' . __NAMESPACE__ . '[/:action]',
							'defaults' => array(
								'controller' => __NAMESPACE__ .
										 '\Controller\Admin\Index',
										'action' => 'index'
							)
						)
					)
				)
			)
		)
	),
	
	'controllers' => array(
		'invokables' => array(
			__NAMESPACE__ . '\Controller\Admin\Index' => __NAMESPACE__ .
			 '\Controller\Admin_IndexController'
		)
	),
	'navigation' => array(
		'admin' => array(
			'settings' => array(
				'label' => 'Ajustes',
				'uri' => '#',
				'pages' => array(
					
					'zfj-page-banner' => array(
						'label' => 'Imagénes de páginas',
						'route' => 'zfcadmin/' . __NAMESPACE__
					)
				)
			)
		)
	),
	'ZfjPageBanner' => array(
		'profiler' => array(
			'collectors' => array(
				'zfj_page_banner_links_collector' => 'ZfjPageBanner\\Collector\\UriCollector'
			)
		),
		'toolbar' => array(
			'entries' => array(
				'zfj_page_banner_links_collector' => 'zfj-page-banner/toolbar/zfj-page-banner-links'
			)
		)
	),
	'JoacubUploader' => array(
		'uploads' => array(
			'zfj_banner_page_uploader' => array(
				'max_number_of_files' => 10,
				'maxNumberOfFiles' => 10,
				'dropZone' => new  Expr('$(\'#zfj_banner_page_uploader\')'),
				'acceptFileTypes' => new Expr('/(\.|\/)(jpg|jpeg|png|gif)$/i'),
				'maxFileSize' => 100000000,
				'keywords' => array(
					'zfj_banner_page_uploader'
				),
				'title' => 'Imagenes de cabecera',
				'subtitle' => '<span class="label label-success">Arrastra las imagenes</span> que esten disponibles hacia la <span class="label label-default">página</span> deseada y sueltala automaticamente se agregara'
	
			),
		)
	),
);