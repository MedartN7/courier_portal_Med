<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JsonParserController extends Controller
{
    public function __construct() {
        $this->menu_json_file  = resource_path('settings'. DIRECTORY_SEPARATOR . 'top_menu.json');
        $this->account_json_file  = resource_path('settings'. DIRECTORY_SEPARATOR . 'accounts.json');
    }

    public function menuAction() {
        $jsonData = file_get_contents( $this->menu_json_file );
        $json = json_decode( $jsonData, true );
        return $json;
    }

    public function accountAction() {
        $jsonData = file_get_contents( $this->account_json_file );
        $json = json_decode( $jsonData, true );
        return $json;
    }

    private $menu_json_file;
    private $account_json_file;
}
