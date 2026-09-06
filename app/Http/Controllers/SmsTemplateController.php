<?php

namespace App\Http\Controllers;

use App\Models\SmsTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SmsTemplateController extends Controller
{
    // List Templates + Search + Pagination
    public function index(Request $request)
    {
        $search = $request->input('search');

        $templates = SmsTemplate::when($search, function ($query, $search) {
                $query->where('template_name', 'like', "%$search%")
                      ->orWhere('message', 'like', "%$search%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('sms_templates.index', compact('templates', 'search'));
    }

    // Add Page
    public function create()
    {
        return view('sms_templates.create');
    }

    // Store Template
    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_name' => 'required|unique:sms_templates|max:255',
            'template_type' => ['required', Rule::in(array_keys($this->types()))],
            'message'       => 'required|max:160'
        ]);

        $template = SmsTemplate::create($validated);

        // First template of a type becomes its default automatically.
        if ($request->boolean('is_default') || !SmsTemplate::where('template_type', $template->template_type)->where('is_default', true)->exists()) {
            $template->makeDefault();
        }

        return redirect()->route('sms-templates.index')
            ->with('success', 'SMS template created' . ($template->fresh()->is_default ? ' and set as the default for ' . $this->types()[$template->template_type] . '.' : '.'));
    }

    // Edit Page
    public function edit($id)
    {
        $template = SmsTemplate::findOrFail($id);
        return view('sms_templates.edit', compact('template'));
    }

    // Update Template
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'template_name' => 'required|unique:sms_templates,template_name,' . $id,
            'template_type' => ['required', Rule::in(array_keys($this->types()))],
            'message'       => 'required|max:160'
        ]);

        $template = SmsTemplate::findOrFail($id);
        $template->update($validated);

        if ($request->boolean('is_default')) {
            $template->makeDefault();
        }

        return redirect()->route('sms-templates.index')
            ->with('success', 'SMS template updated successfully.');
    }

    // Mark a template as the default for its type
    public function setDefault($id)
    {
        $template = SmsTemplate::findOrFail($id);
        $template->makeDefault();

        return redirect()->route('sms-templates.index')
            ->with('success', '"' . $template->template_name . '" is now the default '
                . $this->types()[$template->template_type] . ' SMS.');
    }

    // Delete Template
    public function destroy($id)
    {
        $template = SmsTemplate::findOrFail($id);
        $wasDefault = $template->is_default;
        $type = $template->template_type;

        $template->delete();

        // Never leave a type without a default.
        $message = 'SMS template deleted successfully.';

        if ($wasDefault && $next = SmsTemplate::where('template_type', $type)->orderBy('id')->first()) {
            $next->makeDefault();
            $message .= ' "' . $next->template_name . '" is now the default.';
        }

        return redirect()->route('sms-templates.index')->with('success', $message);
    }

    /** The event types a template can be tagged with. */
    private function types(): array
    {
        return config('templates.types', ['birthday' => 'Birthday']);
    }

    // AJAX Preview
    public function preview($id)
    {
        $template = SmsTemplate::findOrFail($id);
        return response()->json(['message' => $template->message]);
    }
}
