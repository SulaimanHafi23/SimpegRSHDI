<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DocumentType;
use App\Models\DepartmentDocumentType;
use Illuminate\Http\Request;

class DepartmentDocumentTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view-master-data')->only(['index', 'show']);
        $this->middleware('permission:create-master-data')->only(['create', 'store']);
        $this->middleware('permission:edit-master-data')->only(['edit', 'update']);
        $this->middleware('permission:delete-master-data')->only('destroy');
    }
    public function index()
    {
        // Show departments that have at least one mapping, display as cards
        $departments = Department::has('documentTypes')->with('documentTypes')->orderBy('name')->paginate(20);
        return view('admin.master.department-document-types.index', compact('departments'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $documentTypes = DocumentType::orderBy('name')->get();

        return view('admin.master.department-document-types.create', compact('departments', 'documentTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'department_id' => 'required|uuid|exists:departments,id',
            'document_type_ids' => 'required|array|min:1',
            'document_type_ids.*' => 'required|uuid|exists:document_types,id',
        ]);

        $departmentId = $data['department_id'];
        $selected = $data['document_type_ids'];

        foreach ($selected as $docTypeId) {
            DepartmentDocumentType::firstOrCreate([
                'department_id' => $departmentId,
                'document_type_id' => $docTypeId,
            ]);
        }

        return redirect()->route('admin.master.department-document-types.index')->with('success', 'Relasi berhasil ditambahkan');
    }

    public function edit(string $id)
    {
        // here $id is treated as department id
        $department = Department::findOrFail($id);
        $departments = Department::orderBy('name')->get();
        $documentTypes = DocumentType::orderBy('name')->get();

        $selected = $department->documentTypes()->pluck('document_types.id')->toArray();

        return view('admin.master.department-document-types.edit', compact('department', 'departments', 'documentTypes', 'selected'));
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'document_type_ids' => 'nullable|array',
            'document_type_ids.*' => 'required|uuid|exists:document_types,id',
        ]);

        $department = Department::findOrFail($id);
        $new = $data['document_type_ids'] ?? [];

        // sync using Eloquent many-to-many
        $department->documentTypes()->sync($new);

        return redirect()->route('admin.master.department-document-types.index')->with('success', 'Relasi berhasil diperbarui');
    }

    public function show(string $id)
    {
        // show mappings for a department
        $department = Department::with('documentTypes')->findOrFail($id);
        return view('admin.master.department-document-types.show', compact('department'));
    }

    public function destroy(string $id)
    {
        $item = DepartmentDocumentType::findOrFail($id);
        $item->delete();

        return back()->with('success', 'Relasi berhasil dihapus');
    }
}
