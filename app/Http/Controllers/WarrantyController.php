<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarrantyController extends Controller
{
    /** The form itself is the Livewire `WarrantyForm` component. */
    public function create(Request $request): View
    {
        return view('services.warranty', [
            'seo' => Seo::make('Warranty registration & claims', 'Register your LeatherX product warranty or submit a warranty claim online.')->canonical(route('warranty')),
        ]);
    }
}
