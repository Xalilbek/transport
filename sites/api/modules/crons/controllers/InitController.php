<?php
namespace Controllers;

class InitController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');

        $cronUrls = [
            PROJECT_URL.'/crons/checkexceptions',
            PROJECT_URL.'/crons/filter',
            PROJECT_URL.'/crons/addresses',
            PROJECT_URL.'/crons/statistics',
        ];

        foreach ($cronUrls as $url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);
            curl_exec($ch);
            curl_close($ch);
        }

        echo '<pre>';
        print_r($cronUrls);
        exit;
    }
}
