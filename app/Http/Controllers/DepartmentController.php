<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Log;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //$departments = Department::with('director')->get();

        $employees = Employee::select('id', 'first_name', 'middle_name', 'last_name')->get();

        $departments = Department::with('director')->paginate(10);

        return view('departments.index', compact('departments', 'employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $employees = Employee::select('id', 'first_name', 'middle_name', 'last_name')->get();

        return view('departments.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        // Log::info($request->all());
        $request->validate([
            'name' => 'required|string|max:255|min:3',
            'start_date' => 'nullable|date',
            'director_id' => 'nullable|exists:employees,id|unique:departments,director_id',
        ]);

        $input = $request->all();

        if ($request->ajax()) {
            $new_dep = Department::create($input);
    
            return response()->json([
                'success' => true,
                'message' => 'Department creato con successo!',
                'data' => [
                    'id'         => $new_dep->id,
                    'name'       => $new_dep->name,
                    'start_date' => $new_dep->start_date ? $new_dep->start_date->format('d/m/Y') : '',
                    'director'   => $new_dep->director ?  trim($new_dep->director->first_name. ' ' .$new_dep->director->middle_name. ' ' .$new_dep->director->last_name) : 'N/D',
                    'created_at'    => now()->format('d/m/Y'),
                    'updated_at'    => now()->format('d/m/Y'),
                ],
            ], 200);    
        }

        try {
            // throw new \Exception("Errore simulato");
            Department::create($input);

            return redirect(route('departments.index'));

        } catch (\Exception $e) {

            return back()->with('error', 'Errore durante il salvataggio dei dati.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        $department->load('employees');

        return view('departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required',
            'start_date' => 'nullable|date',
            'director_id' => 'required|exists:employees,id|unique:departments,director_id',
        ]);

        $input = $request->all();
        $department->update($input);

        return redirect(route('departments.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        $department->delete();

        return redirect(route('departments.index'));
    }
}
