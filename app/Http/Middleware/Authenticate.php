<?php

namespace BookStack\Http\Middleware;

use BookStack\Entities\Repos\PageRepo;
use Closure;
use Illuminate\Http\Request;

class Authenticate
{
    private PageRepo $pageRepo;

    public function __construct(PageRepo $pageRepo)
    {
        $this->pageRepo = $pageRepo;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        if ($route->hasParameter('pageSlug')) {
            $bookSlug = $route->parameter('bookSlug');
            $pageSlug = $route->parameter('pageSlug');
            $page = $this->pageRepo->getByOldSlug($bookSlug, $pageSlug);

            if (userCan('page-view', $page)) {
                return $next($request);
            }
        }

        if (!hasAppAccess()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            }

            return redirect()->guest(url('/login'));
        }

        return $next($request);
    }
}
