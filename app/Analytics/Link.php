<?php

namespace App\Analytics;

final readonly class Link
{
    public function __construct(
        public string $label,
        public string $url,
        public bool $isExternal = false,
    ) {}

    /**
     * Create link to route.
     */
    public static function toRoute(string $label, string $route, array $params = [], array $query = []): Link
    {
        $routeParams = [...$params];

        foreach (collect($query)->filter()->all() as $key => $val) {
            $routeParams[$key] = $val;
        }

        return new Link($label, route($route, $routeParams));
    }
}
