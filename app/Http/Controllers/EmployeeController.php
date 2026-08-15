<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class EmployeeController extends Controller
{
    // List ALL Employees + Search + Pagination
    public function index(Request $request)
    {
        $search = $request->input('search');

        $employees = Employee::when($search, function ($query, $search) {
                $query->where('full_name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%")
                      ->orWhere('phone', 'like', "%$search%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('employees.index', compact('employees', 'search'));
    }

    // Show ADD Employee Page
    public function create()
    {
        return view('employees.create');
    }

    // Store Employee
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:employees',
            'phone'     => 'nullable|string|max:20',
            'birthday'  => 'required|date',
            'gender'    => 'required|in:Male,Female,Other',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $data = $request->only([
            'full_name', 'email', 'phone',
            'department', 'designation',
            'birthday', 'gender'
        ]);

        // Handle Profile Image
        if ($request->hasFile('profile_image')) {
            $imageName = time() . '.' . $request->profile_image->extension();
            $request->profile_image->move(public_path('uploads/employees'), $imageName);
            $data['profile_image'] = 'uploads/employees/' . $imageName;
        }

        Employee::create($data);

        return redirect()->route('employees.index')
            ->with('success', 'Employee added successfully!');
    }

    // Show Edit Page
    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        return view('employees.edit', compact('employee'));
    }

    // Update Employee
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:employees,email,' . $id,
            'phone'     => 'nullable|string|max:20',
            'birthday'  => 'required|date',
            'gender'    => 'required|in:Male,Female,Other',
            'status'    => 'required|in:active,inactive',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $employee->full_name = $request->full_name;
        $employee->email     = $request->email;
        $employee->phone     = $request->phone;
        $employee->department = $request->department;
        $employee->designation = $request->designation;
        $employee->birthday  = $request->birthday;
        $employee->gender    = $request->gender;

        // Replace profile image if uploaded
        if ($request->hasFile('profile_image')) {
            if ($employee->profile_image && File::exists(public_path($employee->profile_image))) {
                File::delete(public_path($employee->profile_image));
            }
            $imageName = time() . '.' . $request->profile_image->extension();
            $request->profile_image->move(public_path('uploads/employees'), $imageName);
            $employee->profile_image = 'uploads/employees/' . $imageName;
        }
        $employee->status    = $request->status;
        $employee->save();

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully!');
    }

    // Delete Employee
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        // Remove the uploaded photo too, otherwise it is orphaned on disk.
        if ($employee->profile_image && File::exists(public_path($employee->profile_image))) {
            File::delete(public_path($employee->profile_image));
        }

        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully!');
    }


    // Upcoming birthdays, wrapping around into next year
    public function upcomingBirthdays()
    {
        $today = Carbon::today();

        // Order every birthday by how many days away it is, so December birthdays
        // seen from January come last rather than being filtered out entirely.
        $upcomingBirthdays = Employee::where('status', 'active')
            ->orderByRaw(
                'MOD(DAYOFYEAR(birthday) - DAYOFYEAR(?) + 366, 366)',
                [$today->toDateString()]
            )
            ->get();

        return view('employees.upcoming_birthdays', compact('upcomingBirthdays'));
    }

    // Show Bulk Import Page
    public function bulk()
    {
        return view('employees.bulk');
    }

    // Download a correctly formatted sample CSV
    public function bulkSample()
    {
        $rows = [
            ['full_name', 'email', 'phone', 'department', 'designation', 'birthday', 'gender'],
            ['Adaeze Okafor', 'adaeze@example.com', '2348012345678', 'Finance', 'Accountant', '1993-08-17', 'Female'],
            ['Chinedu Eze', 'chinedu@example.com', '2348098765432', 'IT', 'Developer', '1988-02-04', 'Male'],
        ];

        $csv = '';
        foreach ($rows as $row) {
            $csv .= implode(',', $row) . "\r\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employee-import-sample.csv"',
        ]);
    }

    // Import employees from a CSV file
    public function bulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');

        if (!$handle) {
            return redirect()->route('employees.bulk')->with('error', 'Could not read the uploaded file.');
        }

        $header = fgetcsv($handle);

        if (!$header) {
            fclose($handle);
            return redirect()->route('employees.bulk')->with('error', 'The file appears to be empty.');
        }

        // Match columns by name so the column order in the file does not matter.
        $columns = array_map(fn ($name) => strtolower(trim((string) $name)), $header);

        $imported = 0;
        $skipped  = 0;
        $errors   = [];
        $row      = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $row++;

            if ($imported + $skipped >= 100) {
                $errors[] = 'Stopped at the 100 record limit.';
                break;
            }

            $values = [];
            foreach ($columns as $index => $column) {
                $values[$column] = isset($data[$index]) ? trim((string) $data[$index]) : null;
            }

            if (empty($values['full_name']) && empty($values['email'])) {
                continue; // blank line
            }

            $validator = \Illuminate\Support\Facades\Validator::make($values, [
                'full_name' => 'required|string|max:255',
                'email'     => 'required|email|unique:employees,email',
                'phone'     => 'nullable|string|max:20',
                'birthday'  => 'required|date',
                'gender'    => 'nullable|in:Male,Female,Other',
            ]);

            if ($validator->fails()) {
                $skipped++;
                if (count($errors) < 10) {
                    $errors[] = "Row {$row}: " . $validator->errors()->first();
                }
                continue;
            }

            Employee::create([
                'full_name'   => $values['full_name'],
                'email'       => $values['email'],
                'phone'       => $values['phone'] ?? null,
                'department'  => $values['department'] ?? null,
                'designation' => $values['designation'] ?? null,
                'birthday'    => Carbon::parse($values['birthday'])->toDateString(),
                'gender'      => $values['gender'] ?: 'Other',
                'status'      => 'active',
            ]);

            $imported++;
        }

        fclose($handle);

        return redirect()->route('employees.index')
            ->with('success', "Import finished: {$imported} added, {$skipped} skipped.")
            ->with('import_errors', $errors);
    }
}
