<?php
require_once '../vendor/autoload.php';

$basepath = '/localhost/moodler/web';
$app = new \Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$app['debug'] = true;

$app->get('/', function () use ($app) {
    return 'Hello ';
});

$app->get('/hello/{name}', function ($name) use ($app) {
    return $app['twig']->render('main.twig', array(
        'name' => $name,
    ));
});

$app->run();