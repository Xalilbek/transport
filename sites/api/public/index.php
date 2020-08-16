<?php
error_reporting(1);
ini_set("display_errors", true);
require '../../../settings.php';

ini_set('date.timezone', TIMEZONE);

define('PROJECT_URL', 'https://transport.besfly.com');

class Application extends \Phalcon\Mvc\Application
{
    protected function _registerServices()
    {
        $di = new \Phalcon\DI\FactoryDefault();

        $loader = new \Phalcon\Loader();

        $loader->registerDirs(
            [
                __DIR__ . '/../../../phalcon/acl/',
            ]
        );

        $loader->registerNamespaces(array(
            'Models'        => __DIR__ . '/../../../phalcon/models/',
            'Custom\Models' => __DIR__ . '/../models/',
            'Lib'           => __DIR__ . '/../../../phalcon/library/',
        ))->register();

        define('DEBUG', false);
        if (DEBUG) {
            error_reporting(1);
            (new Phalcon\Debug)->listen();
        }

        $di->set('router', function ()
        {
            $router = new \Phalcon\Mvc\Router();

            $router->setDefaultModule("main");

            $router->add('/:controller/:action/:int', ['module' => 'main', 'controller' => 1, 'action' => 2, 'id' => 3]);
            $router->add('/:controller/:action', ['module' => 'main', 'controller' => 1, 'action' => 2]);
            $router->add('/:controller', ['module' => 'main', 'controller' => 1]);

            foreach (_MODULES as $module) {
                $router->add('/' . $module . '/:controller/:action/:int', ['module' => $module, 'controller' => 1, 'action' => 2, 'id' => 3]);
                $router->add('/' . $module . '/:controller/:action', ['module' => $module, 'controller' => 1, 'action' => 2]);
                $router->add('/' . $module . '/:controller', ['module' => $module, 'controller' => 1]);
            }

            return $router;
        });

        $this->setDI($di);
    }

    public function main()
    {
        $this->_registerServices();

        $regModules         = [];
        $regModules['main'] = [
            'className' => 'Multiple\Module',
            'path'      => '../modules/main/Module.php',
        ];
        foreach (_MODULES as $module) {
            $regModules[$module] = [
                'className' => 'Multiple\Module',
                'path'      => '../modules/' . $module . '/Module.php',
            ];
        }

        $this->registerModules($regModules);

        if (in_array(strtolower(@$_SERVER["HTTP_HOST"]),["trtest2.besfly.com"])) {
            $request = new Phalcon\Http\Request();
            $this->handle($request->getURI());
        }

        echo $this->handle()->getContent();
    }
}

try {
    $application = new Application();
    $application->main();
} catch (Exception $e) {
    echo $e->getMessage(), "\n";
}
