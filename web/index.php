<?php
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\ParameterBag;

require_once '../vendor/autoload.php';

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

    $config = new \Moodler\Config(__DIR__ . '/../config/config.ini');
    $moodler = new \Moodler\Mood($config);
    $moodler->storeMood($mood);
    //return $app->json(array ('mood' => $mood),201);
    $path = $app['url_generator']->generate('today');
    return $app->redirect($path, 302);
})
->bind('moodget');

$app->post('/mood', function (Request $request) use ($app) {
    $mood = $request->request->get('mood');

    /** process mood */
    return $app->json(array ('mood' => $mood),201);
})
->bind('mood');

$app->get('/today', function() use ($app) {
    $config = new \Moodler\Config(__DIR__ . '/../config/config.ini');
    $mood = new \Moodler\Mood($config);
    $list = $mood->getMoods();
    return $app['twig']->render('list.twig', array (
        'list' => $list,
    ));
})
->bind('today');

$app->run();