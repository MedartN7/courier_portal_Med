<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CookiesController extends Controller
{
    public function getTranslations() {
        
        $translations = trans('cookies'); // Tablica z tłumaczeniami
        // dd( $translations );
        return response()->json( $translations ); // Zwróć bezpośrednio tablicę jako JSON
    }
}
