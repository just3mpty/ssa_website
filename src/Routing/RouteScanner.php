<?php

declare(strict_types=1);

namespace Capsule\Routing;

use Capsule\Routing\Attribute\Route;
use Capsule\Routing\Attribute\RoutePrefix;
use ReflectionClass;
use ReflectionMethod;

final class RouteScanner
{
    /**
     * @param list<class-string> $controllerClasses
     */
    public static function register(array $controllerClasses, RouterHandler $router): void
    {
        foreach ($controllerClasses as $class) {
            $rc = new ReflectionClass($class);

            $prefix = '';
            $prefAttr = $rc->getAttributes(RoutePrefix::class)[0] ?? null;
            if ($prefAttr) {
                $prefix = rtrim($prefAttr->newInstance()->prefix, '/');
            }

            foreach ($rc->getMethods(ReflectionMethod::IS_PUBLIC) as $m) {
                foreach ($m->getAttributes(Route::class) as $attr) {
                    /** @var Route $r */
                    $r = $attr->newInstance();

                    $path = $prefix . '/' . ltrim($r->path, '/');
                    $path = $path === '' ? '/' : $path;

                    $compiled = RouteCompiler::compile($path);

                    $router->add(new CompiledRoute(
                        regex: $compiled['regex'],
                        variables: $compiled['vars'],
                        methods: array_values(array_unique(array_map('strtoupper', $r->methods))),
                        controllerClass: $class,
                        controllerMethod: $m->getName(),
                        middlewares: $r->middlewares,
                        name: $r->name
                    ));
                }
            }
        }
    }
}
