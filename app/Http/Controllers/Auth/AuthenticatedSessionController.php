<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response|RedirectResponse
    {
        // Check if this is an API request
        if ($request->is('api/*')) {
            // For API requests, handle authentication manually with rate limiting
            $this->ensureIsNotRateLimited($request);

            $credentials = $request->only('email', 'password');
            
            if (!auth()->attempt($credentials)) {
                $this->throwRateLimitException($request);

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => __('auth.failed'),
                ]);
            }

            // Clear the rate limit after successful authentication
            $this->clearRateLimit($request);

            $user = auth()->user();
            
            // Generate an API token for the user
            $token = $user->createToken('auth_token')->plainTextToken;
            
            // Return a Response object with JSON content
            return new Response(
                json_encode([
                    'success' => true,
                    'message' => 'Authenticated successfully', 
                    'data' => [
                        'user' => $user,
                        'token' => $token,
                        'token_type' => 'Bearer',
                    ],
                ]),
                200,
                ['Content-Type' => 'application/json']
            ); 
        } else {
            // For web requests (like Filament), use the normal authentication flow
            $request->authenticate();
            $request->session()->regenerate();
            return redirect('/admin');
        }
    }

    /**
     * Ensure the login request is not rate limited.
     */
    protected function ensureIsNotRateLimited($request): void
    {
        $throttleKey = $this->getThrottleKey($request);
        
        if (!\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return;
        }

        \Illuminate\Support\Facades\Event::dispatch(new \Illuminate\Auth\Events\Lockout($request));

        $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);

        throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Throw rate limit exception.
     */
    protected function throwRateLimitException($request): void
    {
        $throttleKey = $this->getThrottleKey($request);
        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey);
    }

    /**
     * Clear rate limit after successful authentication.
     */
    protected function clearRateLimit($request): void
    {
        $throttleKey = $this->getThrottleKey($request);
        \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    protected function getThrottleKey($request): string
    {
        return \Illuminate\Support\Str::transliterate(
            \Illuminate\Support\Str::lower($request->input('email')).'|'.$request->ip()
        );
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        // Check if this is an API request
        if ($request->is('api/*')) {
            // For API requests, revoke all tokens for the user
            $request->user()->tokens()->delete();
            return response()->noContent();
        } else {
            // For web requests (like Filament), use sessions
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->noContent();
        }
    }
}
