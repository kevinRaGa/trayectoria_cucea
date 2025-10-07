<?php

namespace Config\Routes;

class Router
{
   private static $routes = [];
   private static $middlewares = [];
   private static $currentParams = [];

   public static function get($path, $callback, $middleware = [])
   {
      self::addRoute('GET', $path, $callback, $middleware);
   }

   public static function post($path, $callback, $middleware = [])
   {
      self::addRoute('POST', $path, $callback, $middleware);
   }

   public static function put($path, $callback, $middleware = [])
   {
      self::addRoute('PUT', $path, $callback, $middleware);
   }

   public static function delete($path, $callback, $middleware = [])
   {
      self::addRoute('DELETE', $path, $callback, $middleware);
   }

   public static function group($prefix, $middleware, $callback)
   {
      $previousPrefix = self::$currentPrefix ?? '';
      $previousMiddleware = self::$currentMiddleware ?? [];

      self::$currentPrefix = $previousPrefix . $prefix;
      self::$currentMiddleware = array_merge($previousMiddleware, (array)$middleware);

      $callback();

      self::$currentPrefix = $previousPrefix;
      self::$currentMiddleware = $previousMiddleware;
   }

   private static function addRoute($method, $path, $callback, $middleware = [])
   {
      $prefix = self::$currentPrefix ?? '';
      $groupMiddleware = self::$currentMiddleware ?? [];

      $fullPath = $prefix . $path;
      $allMiddleware = array_merge($groupMiddleware, (array)$middleware);

      self::$routes[] = [
         'method' => $method,
         'path' => $fullPath,
         'callback' => $callback,
         'middleware' => $allMiddleware,
         'pattern' => self::convertToPattern($fullPath)
      ];
   }

   private static function convertToPattern($path)
   {
      $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
      return '#^' . $pattern . '$#';
   }

   private static function getCurrentUri()
   {
      $uri = $_SERVER['REQUEST_URI'];
      $uri = parse_url($uri, PHP_URL_PATH);

      $scriptName = dirname($_SERVER['SCRIPT_NAME']);
      if ($scriptName !== '/') {
         $uri = str_replace($scriptName, '', $uri);
      }

      $uri = rtrim($uri, '/');
      return $uri === '' ? '/' : $uri;
   }

   private static function getRequestMethod()
   {
      return $_SERVER['REQUEST_METHOD'];
   }

   private static function runMiddlewares($middlewares)
   {
      foreach ($middlewares as $middleware) {
         if (is_string($middleware) && isset(self::$middlewares[$middleware])) {
            $result = call_user_func(self::$middlewares[$middleware]);
            if ($result === false) {
               return false;
            }
         } elseif (is_callable($middleware)) {
            $result = call_user_func($middleware);
            if ($result === false) {
               return false;
            }
         }
      }
      return true;
   }

   public static function middleware($name, $callback)
   {
      self::$middlewares[$name] = $callback;
   }

   public static function params($key = null)
   {
      if ($key === null) {
         return self::$currentParams;
      }
      return self::$currentParams[$key] ?? null;
   }

   public static function dispatch()
   {
      $uri = self::getCurrentUri();
      $method = self::getRequestMethod();

      foreach (self::$routes as $route) {
         if ($route['method'] !== $method) {
            continue;
         }

         if (preg_match($route['pattern'], $uri, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            self::$currentParams = $params;

            if (!self::runMiddlewares($route['middleware'])) {
               return;
            }

            call_user_func_array($route['callback'], $params);
            return;
         }
      }

      // 404 - No encontrado
      http_response_code(404);
      view('404', ['title' => 'Página no encontrada']);
   }

   private static $currentPrefix = '';
   private static $currentMiddleware = [];
}

function view($file, $data = [])
{
   extract($data);
   ob_start();
   include __DIR__ . '/../src/views/' . $file . '.php';
   $content = ob_get_clean();
   include __DIR__ . '/../src/layouts/principal.php';
   return $content;
}

function test($file, $data = [])
{
   extract($data);
   ob_start();
   include __DIR__ . '/../tests/' . $file . '.php';
   $content = ob_get_clean();
   include __DIR__ . '/../src/layouts/principal.php';
   return $content;
}

function redirect($path, $statusCode = 302)
{
   $scriptName = dirname($_SERVER['SCRIPT_NAME']);
   $fullPath = ($scriptName !== '/' ? $scriptName : '') . $path;
   header("Location: $fullPath", true, $statusCode);
   exit;
}

// Cargar rutas de navegación
require_once __DIR__ . '/navigation.php';

Router::dispatch();
