<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AcceptTermsRequest;
use Illuminate\Http\RedirectResponse;

final class TermsController extends Controller
{
    /**
     * Accept terms and conditions.
     */
    public function accept(AcceptTermsRequest $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user) {
            $user->update([
                'terms_accepted_at' => now(),
            ]);
        }

        return back()->with('success', 'Terms of Service accepted successfully.');
    }
}
