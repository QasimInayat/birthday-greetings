<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    // List Email Templates with Search + Pagination
    public function index(Request $request)
    {
        $search = $request->input('search');

        $templates = EmailTemplate::when($search, function ($query, $search) {
                $query->where('template_name', 'like', "%$search%")
                      ->orWhere('subject', 'like', "%$search%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('email_templates.index', compact('templates', 'search'));
    }

    // Show Add Email Template Form
    public function create()
    {
        return view('email_templates.create');
    }

    // Store Email Template
    public function store(Request $request)
    {
        $request->validate([
            'template_name' => 'required|unique:email_templates',
            'template_type' => 'required',
            'subject' => 'required',
            'content' => 'required'
        ]);

        EmailTemplate::create($request->all());

        return redirect()->route('email-templates.index')
            ->with('success', 'Email template created successfully.');
    }

    // Show Edit Form
    public function edit($id)
    {
        $template = EmailTemplate::findOrFail($id);
        return view('email_templates.edit', compact('template'));
    }

    // Update Email Template
    public function update(Request $request, $id)
    {
        $template = EmailTemplate::findOrFail($id);

        $request->validate([
            'template_name' => 'required|unique:email_templates,template_name,' . $id,
            'template_type' => 'required',
            'subject' => 'required|max:255',
            'content' => 'required'
        ]);

        $template->update($request->all());

        return redirect()->route('email-templates.index')
            ->with('success', 'Email template updated successfully.');
    }

    // Delete Template
    public function destroy($id)
    {
        $template = EmailTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('email-templates.index')
            ->with('success', 'Email template deleted successfully.');
    }

    // AJAX Preview Template
    public function preview($id)
    {
        $template = EmailTemplate::findOrFail($id);
        return response()->json(['content' => $template->content]);
    }
}
