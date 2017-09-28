# PHP Router

[![Build Status](https://travis-ci.org/devlibs/routing.svg?branch=master)](https://travis-ci.org/devlibs/routing)
[![Coverage Status](https://coveralls.io/repos/github/devlibs/routing/badge.svg?branch=master)](https://coveralls.io/github/devlibs/routing?branch=master)


A fast and flexible HTTP router for PHP.


## Features

- **Unlimited nested grouping router**
- Easy to design **RESTful API**
- **High scalable and flexible**
- **Full Tests**
- No third-party library dependencies

## Requirements

- PHP >= 5.4


## Install

```
composer require devlibs/routing:dev-master
```

Please add the following repository into `repositories` when `composer` complains about
that `Could not find package devlibs/routing ...`.

```
{
    "type": "git",
    "url": "https://github.com/devlibs/routing.git"
}
```


## Usage

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