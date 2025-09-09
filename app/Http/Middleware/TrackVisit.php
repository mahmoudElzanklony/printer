<?php

namespace App\Http\Middleware;

use App\Models\WebsiteVisit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisit
{
    public $param = 'isWebsiteVisit';

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldTrack($request)) {
            $this->record($request);
        }
        return $next($request);
    }

    protected function shouldTrack(Request $request): bool
    {
        return filter_var($request->input($this->param), FILTER_VALIDATE_BOOLEAN);
    }

    protected function record(Request $request): void
    {
        try {
            $query = $request->query();
            unset($query[$this->param]);

            WebsiteVisit::create([
                'user_id' => optional($request->user())->id,
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
        }
    }
}
