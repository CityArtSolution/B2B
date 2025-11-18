<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslateController extends Controller
{
    public function translate(Request $request)
    {
    $text = $request->text;
    $hindi = (new GoogleTranslate('hi'))->translate($text);
    $urdu  = (new GoogleTranslate('ur'))->translate($text);
    $english  = (new GoogleTranslate('en'))->translate($text);

    return [
        'in' => $hindi,
        'ur'  => $urdu,
        'en'  => $english
    ];
    }
}
