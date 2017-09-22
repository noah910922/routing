<?php

use PHPUnit\Framework\TestCase;
use DevLibs\Routing\Router;
use DevLibs\Routing\Route;
use DevLibs\Routing\RouteInterface;

/**
 * @coversDefaultClass DevLibs\Routing\Router
 */
class RouterTest extends TestCase
{
    private $class;

    /**
     * @covers ::__construct
     */
    public function testEmptyRouter()
    {
        $router = new Router();

        $this->assertEquals([], $this->getPropertyValue($router, 'groups'));
        $this->assertEquals([], $this->getPropertyValue($router, 'routes'));
        $this->assertEquals([], $this->getPropertyValue($router, 'settings'));
        $this->assertEquals([], $this->getPropertyValue($router, 'patterns'));
        $this->assertEquals(null, $this->getPropertyValue($router, 'combinedPattern'));
        $this->assertEquals(1, $this->getPropertyValue($router, 'routesNextIndex'));
        $this->assertEquals(
            ['~<([^:]+)>~', '~<([^:]+):([^>]+)>?~', '~/$~'],
            $this->getPropertyValue($router, 'replacePatterns')
        );
        $this->assertEquals(
            ['([^/]+)', '($2)', ''],
            $this->getPropertyValue($router, 'replacements')
        );

        // another router with specify settings
        $settings = ['name' => 'foo'];
        $anotherRouter = new Router($settings);
        $this->assertEquals($settings, $this->getPropertyValue($anotherRouter, 'settings'));

        return $router;
    }

    /**
     * @covers ::setRouteClass
     *
     * @depends testEmptyRouter
     *
     * @param Router $router
     */
    public function testRouteClass(Router $router)
    {
        // validate default route class
        $this->assertEquals(Route::class, $this->getPropertyValue($router, 'routeClass'));
        // set user-defined route class
        $specifyClass = 'MyRoute';
        Router::setRouteClass($specifyClass);
        $this->assertEquals($specifyClass, $this->getPropertyValue($router, 'routeClass'));
        // reset for testing
        Router::setRouteClass(Route::class);
    }

    /**
     * @covers ::delete
     * @covers ::dispatch
     * @covers ::dispatchInternal
     * @covers ::get
     * @covers ::handle
     * @covers ::patch
     * @covers ::post
     * @covers ::put
     * @covers  DevLibs\Routing\Route::getHandler
     * @covers  DevLibs\Routing\Route::getIsEndWithSlash
     * @covers  DevLibs\Routing\Route::getParams
     * @covers  DevLibs\Routing\Route::getSettings
     * @covers  DevLibs\Routing\Route::setHandler
     * @covers  DevLibs\Routing\Route::setIsEndWithSlash
     * @covers  DevLibs\Routing\Route::setParams
     * @covers  DevLibs\Routing\Route::setSettings
     *
     * @depends testEmptyRouter
     *
     * @param Router $router
     */
    public function testDispatch(Router $router)
    {
        // no registered routes, null will be returns
        $route = $router->dispatch(Router::METHOD_GET, '/');
        $this->assertEquals(null, $route);

        $routes = $this->getRoutes();

        // registers routes
        foreach ($routes as $route) {
            foreach ($route['methods'] as $method) {
                switch ($method) {
                    case Router::METHOD_DELETE:
                        $router->delete($route['path'], $route['handler'], $route['settings']);
                        break;
                    case Router::METHOD_GET:
                        $router->get($route['path'], $route['handler'], $route['settings']);
                        break;
                    case Router::METHOD_PATCH:
                        $router->patch($route['path'], $route['handler'], $route['settings']);
                        break;
                    case Router::METHOD_POST:
                        $router->post($route['path'], $route['handler'], $route['settings']);
                        break;
                    case Router::METHOD_PUT:
                        $router->put($route['path'], $route['handler'], $route['settings']);
                        break;
                    default:
                        $router->handle($method, $route['path'], $route['handler'], $route['settings']);
                        break;
                }
            }

            // dispatch test
            foreach ($route['urls'] as $url) {
                foreach ($url['methods'] as $method => $expectRoute) {
                    /**
                     * @var RouteInterface|null $res
                     */
                    $res = $router->dispatch($method, $url['url']);
                    if (is_null($expectRoute)) {
                        $this->assertEquals(null, $res);
                    } else {
                        /**
                         * @var RouteInterface $expectRoute
                         */
                        // validate handler
                        $this->assertEquals($expectRoute->getHandler(), $res->getHandler());
                        // validate params
                        $this->assertEquals($expectRoute->getParams(), $res->getParams());
                        // validate settings
                        $this->assertEquals($expectRoute->getSettings(), $res->getSettings());
                        // validate whether path is end with slash
                        $this->assertEquals($expectRoute->getIsEndWithSlash(), $res->getIsEndWithSlash());
                    }
                }
            }
        }
    }

    public function getRoutes()
    {
        $methods = [
            Router::METHOD_DELETE,
            Router::METHOD_GET,
            Router::METHOD_HEAD,
            Router::METHOD_OPTIONS,
            Router::METHOD_PATCH,
            Router::METHOD_POST,
            Router::METHOD_PUT,
        ];

        $routes = [];

        // round one
        $route1 = new Route();
        $route1->setHandler('homepage');
        $route1->setIsEndWithSlash(true);
        $routes[] = [
            'methods' => $methods,
            'path' => '/',
            'handler' => $route1->getHandler(),
            'settings' => null,
            'urls' => [
                [
                    'url' => '/',
                    'methods' => [
                        Router::METHOD_DELETE => $route1,
                        Router::METHOD_GET => $route1,
                        Router::METHOD_HEAD => $route1,
                        Router::METHOD_OPTIONS => $route1,
                        Router::METHOD_PATCH => $route1,
                        Router::METHOD_POST => $route1,
                        Router::METHOD_PUT => $route1,
                    ],
                ],
                [
                    'url' => '/404',
                    'methods' => [
                        Router::METHOD_DELETE => null,
                        Router::METHOD_GET => null,
                        Router::METHOD_HEAD => null,
                        Router::METHOD_OPTIONS => null,
                        Router::METHOD_PATCH => null,
                        Router::METHOD_POST => null,
                        Router::METHOD_PUT => null,
                    ],
                ],
            ],
        ];

        // round two with specify settings
        $route2 = new Route();
        $route2->setHandler('settings');
        $route2->setSettings(['name' => 'foo']);
        $routes[] = [
            'methods' => [Router::METHOD_GET],
            'path' => '/settings',
            'handler' => 'settings',
            'settings' => $route2->getSettings(),
            'urls' => [
                [
                    'url' => '/settings',
                    'methods' => [
                        Router::METHOD_DELETE => null,
                        Router::METHOD_GET => $route2,
                        Router::METHOD_HEAD => null,
                        Router::METHOD_OPTIONS => null,
                        Router::METHOD_PATCH => null,
                        Router::METHOD_POST => null,
                        Router::METHOD_PUT => null,
                    ],
                ],
            ],
        ];

        // round three register multiple request methods on one path
        $route3 = new Route();
        $route3->setHandler('multiple request methods');
        $routes[] = [
            'methods' => [
                'GET|HEAD',
                [Router::METHOD_POST, Router::METHOD_PUT],
            ],
            'path' => '/multiple-methods',
            'handler' => $route3->getHandler(),
            'settings' => null,
            'urls' => [
                [
                    'url' => '/multiple-methods',
                    'methods' => [
                        Router::METHOD_DELETE => null,
                        Router::METHOD_GET => $route3,
                        Router::METHOD_HEAD => $route3,
                        Router::METHOD_OPTIONS => null,
                        Router::METHOD_PATCH => null,
                        Router::METHOD_POST => $route3,
                        Router::METHOD_PUT => $route3,
                    ],
                ],
            ],
        ];

        // round four test whether paths is end with slash
        $route4 = new Route();
        $route4->setHandler('slash');
        $route4WithSlash = clone $route4;
        $route4WithSlash->setIsEndWithSlash(true);
        $routes[] = [
            'methods' => [Router::METHOD_GET],
            'path' => '/slash',
            'handler' => $route4->getHandler(),
            'settings' => null,
            'urls' => [
                [
                    'url' => '/slash',
                    'methods' => [
                        Router::METHOD_GET => $route4,
                    ],
                ],
                [
                    'url' => '/slash/', // end with slash
                    'methods' => [
                        Router::METHOD_GET => $route4WithSlash,
                    ],
                ],
            ],
        ];

        // round five
        $route5 = new Route();
        $route5->setHandler('get user list or add user');
        $routes[] = [
            'methods' => [Router::METHOD_GET, Router::METHOD_POST],
            'path' => '/users',
            'handler' => $route5->getHandler(),
            'settings' => null,
            'urls' => [
                [
                    'url' => '/users',
                    'methods' => [
                        Router::METHOD_DELETE => null,
                        Router::METHOD_GET => $route5,
                        Router::METHOD_HEAD => null,
                        Router::METHOD_OPTIONS => null,
                        Router::METHOD_PATCH => null,
                        Router::METHOD_POST => $route5,
                        Router::METHOD_PUT => null,
                    ],
                ],
            ],
        ];

        // round six with named parameter placeholder
        $route6 = new Route();
        $route6->setHandler('delete/get/update user profile');
        $route6->setParams(['user_id' => '1']);
        $routes[] = [ //
            'methods' => [Router::METHOD_DELETE, Router::METHOD_GET, Router::METHOD_PUT],
            'path' => '/users/<user_id:\d+>',
            'handler' => $route6->getHandler(),
            'settings' => [],
            'urls' => [
                [
                    'url' => '/users/1',
                    'methods' => [
                        Router::METHOD_DELETE => $route6,
                        Router::METHOD_GET => $route6,
                        Router::METHOD_HEAD => null,
                        Router::METHOD_OPTIONS => null,
                        Router::METHOD_PATCH => null,
                        Router::METHOD_POST => null,
                        Router::METHOD_PUT => $route6,
                    ],
                ],
                [
                    'url' => '/users/foo',
                    'methods' => [
                        Router::METHOD_DELETE => null,
                        Router::METHOD_GET => null,
                        Router::METHOD_HEAD => null,
                        Router::METHOD_OPTIONS => null,
                        Router::METHOD_PATCH => null,
                        Router::METHOD_POST => null,
                        Router::METHOD_PUT => null,
                    ],
                ],
            ],
        ];

        // round seven with multiple named parameters placeholder
        $route7 = new Route();
        $route7->setParams(['user_id' => '2', 'year' => '2017', 'month' => '09', 'title' => 'first-post']);
        $route7->setHandler("delete/get/update user's post");
        $routes[] = [
            'methods' => [Router::METHOD_DELETE, Router::METHOD_GET, Router::METHOD_PUT],
            'path' => '/users/<user_id:\d+>/posts/<year:\d{4}>/<month:\d{2}>/<title>',
            'handler' => $route7->getHandler(),
            'settings' => [],
            'urls' => [
                [
                    'url' => '/users/2/posts/2017/09/first-post',
                    'methods' => [
                        Router::METHOD_DELETE => $route7,
                        Router::METHOD_GET => $route7,
                        Router::METHOD_HEAD => null,
                        Router::METHOD_OPTIONS => null,
                        Router::METHOD_PATCH => null,
                        Router::METHOD_POST => null,
                        Router::METHOD_PUT => $route7,
                    ],
                ],
            ],
        ];

        return $routes;
    }

    /**
     * @covers ::__construct
     * @covers ::dispatch
     * @covers ::dispatchInternal
     * @covers ::group
     * @covers ::handle
     * @covers  DevLibs\Routing\Route::getHandler
     * @covers  DevLibs\Routing\Route::getSettings
     * @covers  DevLibs\Routing\Route::setHandler
     * @covers  DevLibs\Routing\Route::setIsEndWithSlash
     * @covers  DevLibs\Routing\Route::setParams
     * @covers  DevLibs\Routing\Route::setSettings
     *
     * @depends testEmptyRouter
     *
     * @param Router $router
     */
    public function testGroup(Router $router)
    {
        // group v1 without settings
        $group1 = $router->group('v1');
        $route1 = new Route();
        $route1->setHandler('v1 homepage');
        $group1->handle(Router::METHOD_GET, '', $route1->getHandler());
        $this->assertArrayHasKey('v1', $this->getPropertyValue($router, 'groups'));

        // group v1 round one
        $v1Round1 = $router->dispatch(Router::METHOD_GET, 'v1');
        $this->assertEquals($route1, $v1Round1);

        // group v1 round two
        $v1Round2 = $router->dispatch(Router::METHOD_GET, '/v1');
        $this->assertEquals($route1, $v1Round2);

        $route1WithSlash = clone $route1;
        $route1WithSlash->setIsEndWithSlash(true);

        // group v1 round three
        $v1Round3 = $router->dispatch(Router::METHOD_GET, 'v1/');
        $this->assertEquals($route1WithSlash, $v1Round3);

        // group v1 round four
        $v1Round4 = $router->dispatch(Router::METHOD_GET, '/v1/');
        $this->assertEquals($route1WithSlash, $v1Round4);


        // group v2 with specify settings
        $v2Settings = ['name' => 'bar'];
        $group2 = $router->group('v2', $v2Settings);

        $v2Route1 = new Route();
        $v2Route1->setHandler('v2 homepage');

        $v2Route2 = new Route();
        $v2Route2->setHandler('analyze');
        $v2Route2->setSettings(['user' => 'bar']);

        $group2->handle(Router::METHOD_GET, '/', $v2Route1->getHandler());
        $group2->handle(Router::METHOD_GET, '/analyze', $v2Route2->getHandler(), $v2Route2->getSettings());

        // group v2 round one
        $v2Round1 = $router->dispatch(Router::METHOD_GET, '/v2/');
        $this->assertEquals($v2Route1->getHandler(), $v2Round1->getHandler());
        $this->assertEquals($v2Settings, $v2Round1->getSettings());

        // group v2 round two
        $v2Round2 = $router->dispatch(Router::METHOD_GET, '/v2/analyze');
        $this->assertEquals($v2Route2->getHandler(), $v2Round2->getHandler());
        $this->assertEquals(array_merge_recursive($v2Settings, $v2Route2->getSettings()), $v2Round2->getSettings());
    }

    /**
     * @covers ::__construct
     * @covers ::dispatch
     * @covers ::dispatchInternal
     * @covers ::get
     * @covers ::group
     * @covers ::handle
     * @covers  DevLibs\Routing\Route::getHandler
     * @covers  DevLibs\Routing\Route::setHandler
     * @covers  DevLibs\Routing\Route::setIsEndWithSlash
     * @covers  DevLibs\Routing\Route::setParams
     * @covers  DevLibs\Routing\Route::setSettings
     *
     * @depends testEmptyRouter
     *
     * @param Router $router
     */
    public function testNestedRouter(Router $router)
    {
        // group backend
        $groupBackend = $router->group('admin');
        $backendRoute = new Route();
        $backendRoute->setHandler('backend panel');
        $groupBackend->get('/', $backendRoute->getHandler());
        $this->assertEquals($backendRoute->getHandler(), $router->dispatch(Router::METHOD_GET, '/admin')->getHandler());

        $groupUser = $groupBackend->group('users');
        $userListRoute = new Route();
        $userListRoute->setHandler('user list');
        $groupUser->get('/', $userListRoute->getHandler());
        $this->assertEquals($userListRoute->getHandler(), $router->dispatch(Router::METHOD_GET, '/admin/users')->getHandler());
    }

    /**
     * @param Object $obj
     * @param string $name
     * @return mixed
     */
    private function getPropertyValue(&$obj, $name)
    {
        if (!$this->class) {
            $this->class = new ReflectionClass(Router::class);
        }
        $property = $this->class->getProperty($name);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }
}