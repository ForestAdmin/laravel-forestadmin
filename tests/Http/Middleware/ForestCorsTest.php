<?php


use ForestAdmin\LaravelForestAdmin\Http\Middleware\ForestCors;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;

test('On private network of a preflight request should has the correct headers and a 204 response', function () {
    $cors = new ForestCors(app());
    $response = $cors->handle(
        createRequest('OPTIONS', '/forest/ping', [
            'HTTP_ACCESS_CONTROL_REQUEST_PRIVATE_NETWORK' => 'true',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD'          => 'true',
            'HTTP_ORIGIN'                                 => 'http://api.forestadmin.com',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD'          => 'POST',
        ]),
        fn () => new Response()
    );

    expect($response->headers->get('access-control-allow-origin'))
        ->toEqual('http://api.forestadmin.com')
        ->and($response->headers->get('access-control-allow-credentials'))
        ->toEqual('true')
        ->and(Str::containsAll($response->headers->get('access-control-allow-methods'), ['GET', 'POST', 'PUT', 'DELETE']))
        ->toBeTrue()
        ->and($response->headers->get('access-control-max-age'))
        ->toEqual(86400)
        ->and($response->headers->get('access-control-allow-headers'))
        ->toBeNull()
        ->and($response->headers->get('access-control-allow-private-network'))
        ->toEqual('true')
        ->and($response->getStatusCode())
        ->toEqual(204);
});

test('On a preflight request should return correct headers', function () {
    $cors = new ForestCors(app());
    $response = $cors->handle(
        createRequest('OPTIONS', '/forest/ping', [
            'HTTP_ORIGIN'                        => 'http://api.forestadmin.com',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]),
        fn () => new Response()
    );

    expect($response->headers->get('access-control-allow-origin'))
        ->toEqual('http://api.forestadmin.com')
        ->and($response->headers->get('access-control-allow-credentials'))
        ->toEqual('true')
        ->and(Str::containsAll($response->headers->get('access-control-allow-methods'), ['GET', 'POST', 'PUT', 'DELETE']))
        ->toBeTrue()
        ->and($response->headers->get('access-control-max-age'))
        ->toEqual(86400)
        ->and($response->headers->get('access-control-allow-headers'))
        ->toBeNull()
        ->and($response->getStatusCode())
        ->toEqual(204);
});

test('On a request OPTIONS not preflight, should vary the headers', function () {
    $cors = new ForestCors(app());
    $response = $cors->handle(
        createRequest('OPTIONS', '/forest/ping', [
            'HTTP_ORIGIN' => 'http://api.forestadmin.com',
        ]),
        fn () => new Response()
    );

    expect($response->headers->get('vary'))
        ->toEqual('Access-Control-Request-Method, Origin')
        ->and($response->getStatusCode())
        ->toEqual(200);
});

test('when the route does not start with "forest" headers must not be set', function () {
    $cors = new ForestCors(app());
    $response = $cors->handle(
        createRequest('OPTIONS', '/no-forest/ping', [
            'HTTP_ACCESS_CONTROL_REQUEST_PRIVATE_NETWORK' => 'true',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD'          => 'true',
            'HTTP_ORIGIN'                                 => 'http://api.forestadmin.com',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD'          => 'POST',
        ]),
        fn () => new Response()
    );

    expect($response->headers->get('access-control-allow-origin'))
        ->toBeNull()
        ->and($response->headers->get('access-control-allow-credentials'))
        ->toBeNull()
        ->and($response->headers->get('access-control-allow-methods'))
        ->toBeNull()
        ->and($response->headers->get('access-control-max-age'))
        ->toBeNull()
        ->and($response->headers->get('access-control-allow-headers'))
        ->toBeNull()
        ->and($response->getStatusCode())
        ->toEqual(200);
});

function createRequest(string $method, string $uri, $headers = []): Request
{
    $symfonyRequest = SymfonyRequest::create($uri, $method, [], [], [], $headers);

    return Request::createFromBase($symfonyRequest);
}
