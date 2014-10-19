<?php
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\ParameterBag;

require_once '../vendor/autoload.php';

date_default_timezone_set('Europe/Brussels');

defined('APP_ENV') ||
    define('APP_ENV', isset ($_SERVER['APP_ENV']) ? $_SERVER['APP_ENV'] : 'production');

$config = new \Moodler\Config(__DIR__ . '/../config/config.ini', APP_ENV);

$client = new Raven_Client($config->getSentryUrl());

$error_handler = new Raven_ErrorHandler($client);
// Register error handler callbacks
set_error_handler(array($error_handler, 'handleError'));
set_exception_handler(array($error_handler, 'handleException'));

$basepath = '/localhost/moodler/web';
$app = new \Silex\Application();

$app['debug'] = true;

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

$app->get('/', function () use ($app) {
    return $app['twig']->render('main.twig');
})
->bind('home');

$app->get('/mood/{mood}', function ($mood) use ($app, $config, $client) {
    $moodler = new \Moodler\Mood($config);
    try {
        $moodler->storeMood($mood);
    } catch (\InvalidArgumentException $e) {
        $client->captureException($e);
    } catch (\RuntimeException $e) {
        $client->captureException($e);
    }
    $path = $app['url_generator']->generate('today');
    return $app->redirect($path, 302);
})
->bind('moodget');

$app->get('/today', function() use ($app, $config, $client) {
    $mood = new \Moodler\Mood($config);
    $list = $mood->getMoods();
    return $app['twig']->render('list.twig', array (
        'list' => $list,
    ));
})
->bind('today');

$app->run();