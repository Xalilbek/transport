<?php
namespace Multiple;

define("CRM_TYPE", 3);
define("BUSINESS_ID", ['$ne' => -1]);
define("TOKEN", "");

class Module
{

	public function registerAutoloaders()
	{
		$loader = new \Phalcon\Loader();

		$loader->registerNamespaces([
			'Controllers' => __DIR__."/controllers",
		]);

		$loader->register();
	}

	public function registerServices($di)
	{
	    $di->set('dispatcher', function () {
			$dispatcher = new \Phalcon\Mvc\Dispatcher();

			//$eventManager = new \Phalcon\Events\Manager();
			//$eventManager->attach('dispatch', new \AclApi('crons'));

			//$dispatcher->setEventsManager($eventManager);
			$dispatcher->setDefaultNamespace("Controllers");
			return $dispatcher;
		});


        $di->set('view', function () {
            $view = new \Phalcon\Mvc\View();
            return $view;
        });
	}

}