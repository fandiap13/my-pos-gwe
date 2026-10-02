<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\DetermineLoginRedirectAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request, DetermineLoginRedirectAction $determineRedirect): RedirectResponse|Response
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->to($determineRedirect->handle($request->user()))
                    : Inertia::render('Auth/VerifyEmail', ['status' => session('status')]);
    }
}
