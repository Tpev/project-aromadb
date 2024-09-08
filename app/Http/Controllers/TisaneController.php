<?php
namespace App\Http\Controllers;

use App\Models\Tisane;
use Illuminate\Http\Request;

class TisaneController extends Controller
{
    public function index()
    {
        $tisanes = Tisane::all();
        return view('tisanes.index', compact('tisanes'));
    }

    public function show($id)
    {
        $tisane = Tisane::findOrFail($id);
        return view('tisanes.show', compact('tisane'));
    }
}
