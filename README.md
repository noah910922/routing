# PHP Router [![Build Status](https://travis-ci.org/devlibs/routing.svg?branch=master)](https://travis-ci.org/devlibs/routing) [![Coverage Status](https://coveralls.io/repos/github/devlibs/routing/badge.svg?branch=master)](https://coveralls.io/github/devlibs/routing?branch=master)

A fast, flexible and scalable HTTP router for PHP.

## Features

- **Unlimited nested grouping router**
- **Easy to design RESTful API**
- **Full Tests**
- **Flexible and scalable**
- **No third-party library dependencies**
- **Named Param Placeholder**
- **Detect all request methods of the specify path**

## Requirements

- PHP >= 5.4

## Install

```
composer require devlibs/routing:dev-master
```

## Documentation

```php
include '/path-to-vendor/autoload.php';

use DevLibs\Routing\Router;

// create an router instance
$router = new Router();
```

### Register handler

```php
Router::handle($method, $path, $handler, $settings = null);
```

- $method - `string`/`array`, case-sensitive, such as `GET`, `GET|POST`, `['GET', 'POST']`.
- $path - the path `MUST` start with slash `/`, such as `/`, `/users`, `/users/<username>`.
- $handler - `mixed`, whatever you want.
- $settings - user-defined settings.

Examples

| Method            | Path                         | Handler | Matched                            | Unmatched                              |
|:------------------|:-----------------------------|:--------|:-----------------------------------|----------------------------------------|
| GET               | /                            | any     | `GET /`                            | `POST /` `get /`                       |
| GET|POST          | /users                       | any     | `GET /users` `POST /users`         |                                        |
| ['GET', 'POST']   | /posts                       | any     | `GET /posts` `POST /posts`         |                                        |
| GET               | /users/<username>            | any     | `GET /users/foo` `GET /users/bar`  |                                        |
| GET               | /orders/<order_id:\d+>       | any     | `GET /orders/123456`               | `GET /orders/letters`                  |

We also provides a few shortcuts for registering handler:

- Router::delete
- Router::get
- Router::post
- Router::put

```
use DevLibs\Routing\Router;

$router = new Router();

// the path MUST start with slash '/'
$path = '/';
$router->handle('GET', $path, 'hello world');

// dispatch
$url = '/';
$route = $
```
```
// RESTful API
$router->get('users', 'users list'); // GET "users" matched
$router->post('users', 'create user account'); // POST "users" matched
$router->get('users/<username>', 'users profile'); // GET "users/foo" matched
$router->delete('users/<username>', 'delete user'); // DELETE "users/bar" matched
$router->get('posts/<post_id:\d+>', 'post content'); // GET "posts/1" matched, GET "posts/nan" unmatched

// Grouping
$router->group('admin', function (Router $group) {
    $group->get('', 'admin dashboard');
});

// Dispatch request
$requestMethod = 'GET';
$requestPath = 'users/foo';

// If matched, $route contains handler, params and settings, otherwise $route is null.
// In this case:
// $route = [
//     'handler', // first elements
//     'params', // second elements
//     'settings', // third elements
// ];
$route = $router->dispatch($requestMethod, $requestPath);
```

### Grouping Router


### Params Placeholder


### RESTful API


### Scalable


## FAQ

### Package Not Found

Please add the following repository into `repositories` when `composer` complains about
that `Could not find package devlibs/routing ...`.

```json
{
    "type": "git",
    "url": "https://github.com/devlibs/routing.git"
}
```