<?php
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\ParameterBag;

require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Europe/Brussels');

defined('APP_ENV') ||
    define('APP_ENV', isset ($_SERVER['APP_ENV']) ? $_SERVER['APP_ENV'] : 'production');

if (!file_exists(__DIR__ . '/../config/config.ini')) {
    echo "Configuration not provisioned yet";
    exit;
}
$config = new \Moodler\Config(__DIR__ . '/../config/config.ini', APP_ENV);

$client = new Raven_Client($config->getSentryUrl());

$error_handler = new Raven_ErrorHandler($client);
// Register error handler callbacks
set_error_handler(array($error_handler, 'handleError'));
set_exception_handler(array($error_handler, 'handleException'));

$logfile = __DIR__ . sprintf('/../logs/moodler-%d.log', date('Ym'));
$logger = new \Moodler\Logger($logfile, \Moodler\Logger::LOG_LEVEL_DEBUG);

$basepath = '/localhost/moodler/web';
$app = new \Silex\Application();

$app['debug'] = false;

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

$app->get('/mood/{mood}', function ($mood) use ($app, $config, $client, $logger) {
    $moodler = new \Moodler\Mood($config);
    $moodler->setLogger($logger);
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

$app->get('/today', function() use ($app, $config, $client, $logger) {
    $mood = new \Moodler\Mood($config);
    $mood->setLogger($logger);
    $list = $mood->getMoods();
    return $app['twig']->render('list.twig', array (
        'list' => $list,
    ));
})
->bind('today');

$app->run();