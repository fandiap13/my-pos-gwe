<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\DetermineLoginRedirectAction;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request, DetermineLoginRedirectAction $determineRedirect): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->to($determineRedirect->handle($request->user()).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->to($determineRedirect->handle($request->user()).'?verified=1');
    }
}
