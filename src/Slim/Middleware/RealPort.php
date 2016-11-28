<?php
namespace Wirecard\WcsIntegrationExample\Slim\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Middleware class RealPort
 * Detects if request is forwarded by reverse proxy and set the correct port from header
 *
 * @package Wirecard\WcsIntegrationExample\Slim\Middleware
 */
class RealPort
{
    /**
     * if no port is detected we should use port 80
     */
    const DEFAULT_PORT = 80;

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @throws \InvalidArgumentException
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $serverParams = $request->getServerParams();
        $uri = $this->createUri($request->getUri(), $serverParams);
        $request = $request->withUri($uri);
        return $next($request, $response);
    }

    /**
     * Create the correct uri for the request, if a reverse proxy was detected
     *
     * @param UriInterface $uri
     * @param $serverParams
     * @return UriInterface
     * @throws \InvalidArgumentException
     */
    private function createUri(UriInterface $uri, $serverParams)
    {
        $serverPort = $this->getServerPort($serverParams);
        if (array_key_exists('HTTP_X_FORWARDED_PROTO', $serverParams)) {
            $uri = $uri->withScheme($serverParams['HTTP_X_FORWARDED_PROTO']);
        }
        return $uri->withPort($serverPort);
    }

    /**
     * this method returns the correct server port based on the server's parameters
     *
     * if we detect a http_x_forwarded_port, we return it as the correct port
     * if we detect the http_x_forwarded_proto to be set, we return 443 for https and 80 for http as the correct port
     * if no reverse proxy was detected, we return the already used server_port, or if it's not set a default port (80)
     *
     * @param $serverParams
     * @return int
     */
    private function getServerPort($serverParams)
    {
        //detect if request is forwared at all
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $serverParams)) {
            //detect if forward protocol is set (otherwise we expect http
            if (array_key_exists('HTTP_X_FORWARDED_PROTO', $serverParams)) {
                $forwardedProtocolPort = $serverParams['HTTP_X_FORWARDED_PROTO'] === 'https' ? 443 : 80;
            } else {
                $forwardedProtocolPort = self::DEFAULT_PORT;
            }
            //detect if forward port is set. If so we use it, otherwise we use the default port for the protocol
            $port = array_key_exists('HTTP_X_FORWARDED_PORT', $serverParams) ? $serverParams['HTTP_X_FORWARDED_PORT'] : $forwardedProtocolPort;
        } else {
            //use default server port
            $port = array_key_exists('SERVER_PORT', $serverParams) ? $serverParams['SERVER_PORT'] : self::DEFAULT_PORT;
        }
        //integer cast, because PSR request expects it to be integer
        return (int)$port;

    }
}