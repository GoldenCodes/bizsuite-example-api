<?php
ini_set('display_errors', 1);
error_reporting(-1);

# ------------- IMPORT FILES -------------
require_once __DIR__.'/vendor/autoload.php';
$config       = require_once 'config.php';
$access_token = file_exists('access_token.php') ? include_once 'access_token.php' : '';
use Symfony\Component\HttpFoundation\Request;


# ------------- INIT SILEX -------------
$app = new Silex\Application();
$app['debug'] = true;
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));


# ------------- SERVICES -------------

$app['create_api_url'] = $app->protect(function ($uri, $application_url=null) use ($config) {
    return 'http://'.(!empty($application_url) ? "$application_url." : '').$config['url_api'].$uri;
});


# ------------- ACTIONS -------------
$app->get('/', function (Request $request) use ($app, $config) {

    $scope = implode(' ', $config['scopes']);
    $url = "http://{$config['url_site']}/oauth/authorize?client_id={$config['client_id']}&response_type=code&redirect_uri={$config['redirect_uri']}&scope={$scope}";

    return $app['twig']->render('oauth.twig', array(
        'url' => $url
    ));

});

$app->get('/oauth/callback', function (Request $request) use ($app, $config) {

    try {

        $http = new GuzzleHttp\Client;

        $code = $request->get ('code');
        if (!empty($code)) {
            $response = $http->post($app['create_api_url']('/oauth/token'), [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => $config['client_id'],
                    'client_secret' => $config['client_secret'],
                    'redirect_uri' => $config['redirect_uri'],
                    'code' => $code,
                ],
            ]);

            $result = json_decode((string) $response->getBody(), true);
            if (empty($result['access_token'])) {
                throw new Exception('Error: access_token not found');
            }

            $res = file_put_contents('access_token.php', '<?php return '.var_export($result, true).';');
            if (empty($res)) {
                throw new Exception('Error: não foi possivel salvar o arquivo access_token.php');
            }

            return $app->redirect('/list-products');

        } else {
            throw new Exception('Error: $code not found');
        }


    } catch (\GuzzleHttp\Exception\ClientException $e) {

        if ($e->hasResponse ()) {

            $return = json_decode((string) $e->getResponse ()->getBody (), true);

            echo '<pre>';
            print_r($return);
            echo '</pre>';

        } else {
            echo $e->getMessage ();
        }

        exit;

    } catch (Exception $e) {
        echo $e->getMessage ();
        exit;
    }

});



$app->get('/list-products', function (Request $request) use ($app, $access_token) {

    if (empty($access_token['access_token']))
        return $app->redirect('/');


    $headers = [
        'Authorization' => 'Bearer ' . $access_token['access_token'],
        'Accept'        => 'application/json',
    ];
    $http = new GuzzleHttp\Client;
    $response = $http->get(
        $app['create_api_url']('/products', $access_token['application']['url']),
        [
            'headers' => $headers
        ]
    );


    echo '<pre>';
    print_r(json_decode($response->getBody(), true));
    echo '</pre>';
    exit;
});


$app->run();