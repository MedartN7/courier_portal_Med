<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BusinessCardController extends Controller
{
    public function show() {
        $this->settings = include resource_path('settings/businessCardSettings.php');
        return view('business_card')
            ->with('settings', $this->settings);
    }

    public array $settings;
}
