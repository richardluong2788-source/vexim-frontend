<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class AuditMiddleware
{
    /**
     * Handle an incoming request and log it
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log certain routes or methods
        if ($this->shouldLog($request)) {
            $this->logRequest($request, $response);
        }

        return $response;
    }

    /**
     * Determine if request should be logged
     */
    private function shouldLog(Request $request): bool
    {
        // Don't log GET requests or health checks
        if ($request->isMethod('GET') || $request->is('api/health')) {
            return false;
        }

        // Don't log login attempts (handled separately)
        if ($request->is('api/auth/*')) {
            return false;
        }

        return true;
    }

    /**
     * Log the request
     */
    private function logRequest(Request $request, $response)
    {
        try {
            $action = $this->determineAction($request);
            
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'auditable_type' => 'Request',
                'auditable_id' => 0,
                'old_values' => null,
                'new_values' => $request->except(['password', 'password_confirmation']),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "{$request->method()} {$request->path()}",
            ]);
        } catch (\Exception $e) {
            // Silently fail to not interrupt the request
            \Log::error('Audit logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Determine action from request
     */
    private function determineAction(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        return strtolower($method) . '_' . str_replace('/', '_', $path);
    }
}
