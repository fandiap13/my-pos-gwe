<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\DetermineLoginRedirectAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request, DetermineLoginRedirectAction $determineRedirect): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->to($determineRedirect->handle($request->user()));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
