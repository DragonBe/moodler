<?php
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\ParameterBag;

require_once '../vendor/autoload.php';

defined('APP_ENV') ||
    define('APP_ENV', isset ($_SERVER['APP_ENV']) ? $_SERVER['APP_ENV'] : 'production');

$client = new Raven_Client('https://cf9f4d8b9ff643eb96b3ac019211d732:173bf2a133144fd0a4f93a4f1a361839@app.getsentry.com/31069');
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

$app->get('/mood/{mood}', function ($mood) use ($app) {

    $config = new \Moodler\Config(__DIR__ . '/../config/config.ini', APP_ENV);
    $moodler = new \Moodler\Mood($config);
    $moodler->storeMood($mood);
    $path = $app['url_generator']->generate('today');
    return $app->redirect($path, 302);
})
->bind('moodget');

$app->get('/today', function() use ($app) {
    $config = new \Moodler\Config(__DIR__ . '/../config/config.ini', APP_ENV);
    $mood = new \Moodler\Mood($config);
    $list = $mood->getMoods();
    return $app['twig']->render('list.twig', array (
        'list' => $list,
    ));
})
->bind('today');

$app->run();