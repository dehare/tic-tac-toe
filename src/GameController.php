<?php

namespace Dehare\TicTacGame;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Yaml\Yaml;

class GameController
{
    /** @var Game */
    private $game;

    /** @var Request */
    private $request;

    /** @var Session */
    private $session;

    public function __construct()
    {
        $this->request = Request::createFromGlobals();
        $this->session = new Session();
        $this->session->start();

        $this->game = new Game($this->session->get('board', []));

        $this->handleRequest();
    }

    public function render($view)
    {
        $view     = $view ?: 'template';
        $template = __DIR__ . "/../resources/$view.php";
        if (!file_exists($template)) {
            throw new FileNotFoundException("Template $view.php could not be found");
        }
        return file_get_contents(__DIR__ . "/../resources/$view.php");
    }

    public function restart()
    {
        $this->session->clear();
        $this->game = new Game();

        $this->render('template');
    }

    protected function handleRequest()
    {
        $route = $this->getCurrentRoute();
        if (!$route) {
            throw new \Exception('No matching route not found for ' . $this->request->getPathInfo(), 503);
        }
        $controller = ($route['action']['controller'] ?? null);
        $action     = $route['action']['action'] ?? null;
        $params     = $route['params'];

        $method = [$controller, $action];
        if (!$controller || $controller == 'controller') {
            $method = [get_class($this), $action];
        }
        if (!$controller && !$action) {
            $method = [];
        }

        $this->dispatch($method, $params);
    }

    /**
     * @return bool|array
     */
    private function getCurrentRoute()
    {
        $requestPath    = trim($this->request->getPathInfo());
        $requestMethods = [
            'ajax' => $this->request->isXmlHttpRequest(),
            'get'  => $this->request->isMethod('GET'),
            'post' => $this->request->isMethod('POST'),
        ];

        // validate all methods within config
        $validateMethods = function ($route) use ($requestMethods) {
            $result = true;
            foreach ($route['methods'] as $method) {
                $result = $result && $requestMethods[$method];
            }
            return $result;
        };

        $config   = Yaml::parseFile(__DIR__ . '/../resources/routes.yml');
        $getBody  = $this->request->query->all();
        $postBody = $this->request->request->all();

        foreach ($config as $name => $route) {
            if (($route['path'] === $requestPath && $validateMethods($route)) &&
                ($route['body'] && ($getBody || $postBody) || (!$route['body'] && !$getBody && !$postBody))
            ) {
                $route['params'] = $this->getRequestParams($route['action']['params'] ?? []);
                return $route;
            }
        }

        return false;
    }

    /**
     * @param array  $params
     * @param string $method
     *
     * @return array
     */
    private function getRequestParams(array $params)
    {
        $requestParams = [];
        foreach ($params as $key) {
            if ($this->request->isMethod('GET')) {
                $requestParams[$key] = $this->request->get($key);
            } elseif ($this->request->isMethod('POST')) {
                $requestParams[$key] = $this->request->request->get($key);
            }
        }

        return $requestParams;
    }

    /**
     * @param array $method
     * @param array $params
     */
    private function dispatch(array $method, array $params = [])
    {
        $response = new Response(null);

        if (!empty($method)) {
            if (is_string($method[0]) && !class_exists($method[0])) {
                if (!isset($this->{$method[0]})) {
                    throw new \InvalidArgumentException('Unknown controller ' . $method[0], 503);
                }
                $method[0] = $this->{$method[0]};
            }

            if (!method_exists($method[0], $method[1])) {
                throw new \InvalidArgumentException(sprintf('Unknown method %s::%s', get_class($method[0]), $method[1]), 503);
            }

            $response = call_user_func_array($method, $params);
        }

        if (!$response instanceof Response) {
            if ($this->request->isXmlHttpRequest() && !is_object($response)) {
                if (is_bool($response)) {
                    $response = new JsonResponse(['result' => $response], $response ? 200 : 500);
                } else {
                    $response = new JsonResponse(is_array($response) ? $response : ['message' => $response]);
                }
            } elseif (is_string($response)) {
                $response = new Response($response);
            }
        }

        if (!$response instanceof Response) {
            throw new \InvalidArgumentException('Illegal response for ' . $this->request->getPathInfo());
        }

        $this->session->set('board', $this->game->getBoard(true)); // save gameboard on every request

        $response->send();
    }
}
