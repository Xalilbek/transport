<?php
namespace Multiple;

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

			$eventManager = new \Phalcon\Events\Manager();
			$eventManager->attach('dispatch', new \AclApi('frontend'));

			$dispatcher->setEventsManager($eventManager);
			$dispatcher->setDefaultNamespace("Controllers");
			return $dispatcher;
		});


        $di->set('view', function () {
            $view = new \Phalcon\Mvc\View();
            return $view;
        });
	}

}