<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    function __construct(){
        $this->token = session('api_token');
    }
    function isAuthenticated(){
        return session()->has('api_token');
    }
    /**
     * Handle an incoming authentication request.
     */
    // public function store(LoginRequest $request): RedirectResponse
    // {
    //     // Call your API endpoint
    //     $response = Http::post(config('app.url') . '/api/users/login', [
    //         'email'    => $request->email,
    //         'password' => $request->password,
    //     ]);
    
    //     if ($response->successful()) {
    //         $responseBody = $response->json();
    
    //         // If API returned a token and user data
    //         if (!empty($responseBody['token'])) {
    //             // Log the user in via Laravel's Auth system
    //             Auth::loginUsingId($responseBody['user']['id']);
    
    //             // Regenerate session
    //             $request->session()->regenerate();
    
    //             // Redirect to homepage
    //             return redirect()->intended('/');
    //         }
    
    //         // If API responded but no token/user
    //         return back()->withErrors([
    //             'message' => $responseBody['message'] ?? 'Login failed',
    //         ]);
    //     }
    
    //     // If API call failed entirely
    //     return back()->withErrors([
    //         'message' => 'Unable to reach authentication service',
    //     ]);
    // }
    
    public function store(LoginRequest $request): RedirectResponse
    {
        // This uses Laravel's built-in authentication
        $request->authenticate();
    
        $request->session()->regenerate();
    
        return redirect()->intended(route('dashboard', absolute: false));
    }
    
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
