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
        $validated = $request->validate([
            'template_name' => 'required|unique:email_templates',
            'template_type' => ['required', \Illuminate\Validation\Rule::in(array_keys(config('templates.types')))],
            'subject' => 'required',
            'content' => 'required'
        ]);

        $template = EmailTemplate::create($validated);

        // First template of a type becomes its default automatically.
        if ($request->boolean('is_default') || !EmailTemplate::where('template_type', $template->template_type)->where('is_default', true)->exists()) {
            $template->makeDefault();
        }

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

        $template->update($request->only(['template_name','template_type','subject','content']));

        if ($request->boolean('is_default')) {
            $template->makeDefault();
        }

        return redirect()->route('email-templates.index')
            ->with('success', 'Email template updated successfully.');
    }

    // Mark a template as the default for its type
    public function setDefault($id)
    {
        $template = EmailTemplate::findOrFail($id);
        $template->makeDefault();

        $label = config('templates.types')[$template->template_type] ?? $template->template_type;

        return redirect()->route('email-templates.index')
            ->with('success', '"' . $template->template_name . '" is now the default ' . $label . ' email.');
    }

    // Delete Template
    public function destroy($id)
    {
        $template = EmailTemplate::findOrFail($id);
        $wasDefault = $template->is_default;
        $type = $template->template_type;

        $template->delete();

        // Never leave a type without a default.
        $message = 'Email template deleted successfully.';

        if ($wasDefault && $next = EmailTemplate::where('template_type', $type)->orderBy('id')->first()) {
            $next->makeDefault();
            $message .= ' "' . $next->template_name . '" is now the default.';
        }

        return redirect()->route('email-templates.index')->with('success', $message);
    }

    // AJAX Preview Template
    public function preview($id)
    {
        $template = EmailTemplate::findOrFail($id);
        return response()->json(['content' => $template->content]);
    }
}
