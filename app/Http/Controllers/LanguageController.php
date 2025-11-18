<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch the application language
     */
    public function switch(Request $request, $locale)
    {
        // Validate the locale
        if (!in_array($locale, ['en', 'de'])) {
            abort(400, 'Invalid locale');
        }

        // Set the locale
        App::setLocale($locale);
        
        // Store in session for persistence
        Session::put('locale', $locale);

        // Redirect back to the previous page or to a default page
        $previousUrl = $request->header('referer');
        if ($previousUrl) {
            return redirect($previousUrl);
        }
        
        return redirect()->route('welcome');
    }
}
