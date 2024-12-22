<?php
namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
		    if (!auth()->user() || !auth()->user()->isAdmin()) {
        return redirect('/')->with('error', 'Unauthorized access');
    }
        $templates = EmailTemplate::all();
        return view('admin.marketing.templates.index', compact('templates'));
    }

    public function store(Request $request)
    {
		    if (!auth()->user() || !auth()->user()->isAdmin()) {
        return redirect('/')->with('error', 'Unauthorized access');
    }
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        EmailTemplate::create($request->only(['name', 'content']));

        return redirect()->route('admin.marketing.templates')->with('success', 'Template created successfully!');
    }

    public function edit($id)
    {
		    if (!auth()->user() || !auth()->user()->isAdmin()) {
        return redirect('/')->with('error', 'Unauthorized access');
    }
        $template = EmailTemplate::findOrFail($id);
        return view('admin.marketing.templates.edit', compact('template'));
    }

    public function update(Request $request, $id)
    {
		    if (!auth()->user() || !auth()->user()->isAdmin()) {
        return redirect('/')->with('error', 'Unauthorized access');
    }
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $template = EmailTemplate::findOrFail($id);
        $template->update($request->only(['name', 'content']));

        return redirect()->route('admin.marketing.templates')->with('success', 'Template updated successfully!');
    }
}
