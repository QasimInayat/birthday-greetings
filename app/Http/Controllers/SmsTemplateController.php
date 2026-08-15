<?php

namespace App\Http\Controllers;

use App\Models\SmsTemplate;
use Illuminate\Http\Request;

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
        $request->validate([
            'template_name' => 'required|unique:sms_templates|max:255',
            'message'       => 'required|max:160'
        ]);

        SmsTemplate::create($request->all());

        return redirect()->route('sms-templates.index')
            ->with('success', 'SMS template created successfully.');
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
        $request->validate([
            'template_name' => 'required|unique:sms_templates,template_name,' . $id,
            'message'       => 'required|max:160'
        ]);

        $template = SmsTemplate::findOrFail($id);
        $template->update($request->all());

        return redirect()->route('sms-templates.index')
            ->with('success', 'SMS template updated successfully.');
    }

    // Delete Template
    public function destroy($id)
    {
        SmsTemplate::findOrFail($id)->delete();

        return redirect()->route('sms-templates.index')
            ->with('success', 'SMS template deleted successfully.');
    }

    // AJAX Preview
    public function preview($id)
    {
        $template = SmsTemplate::findOrFail($id);
        return response()->json(['message' => $template->message]);
    }
}
