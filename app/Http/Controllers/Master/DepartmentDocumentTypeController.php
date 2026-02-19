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
        $this->middleware('permission:department-document-type.manage')->only(['index', 'show']);
        $this->middleware('permission:department-document-type.manage')->only(['create', 'store']);
        $this->middleware('permission:department-document-type.manage')->only(['edit', 'update']);
        $this->middleware('permission:department-document-type.manage')->only('destroy');
    }
    public function index()
    {
        // Show departments that have at least one mapping, display as cards
        $departments = Department::has('documentTypes')->with('documentTypes')->orderBy('name')->paginate(20);
        $universalDocumentTypes = DocumentType::where('is_universal', true)
            ->orderBy('name')
            ->get();

        return view('admin.master.department-document-types.index', compact('departments', 'universalDocumentTypes'));
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
            'department_id' => 'required',
            'document_type_ids' => 'required|array|min:1',
            'document_type_ids.*' => 'required|uuid|exists:document_types,id',
        ]);

        $departmentId = $data['department_id'];
        $selected = $data['document_type_ids'];

        if ($departmentId === 'universal') {
            foreach ($selected as $docTypeId) {
                DocumentType::whereKey($docTypeId)->update(['is_universal' => true]);
                DepartmentDocumentType::where('document_type_id', $docTypeId)->delete();
            }

            return redirect()
                ->route('admin.master.department-document-types.index')
                ->with('success', 'Relasi universal berhasil ditambahkan');
        }

        if (! Department::whereKey($departmentId)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['department_id' => 'Departemen tidak valid']);
        }

        foreach ($selected as $docTypeId) {
            DepartmentDocumentType::firstOrCreate([
                'department_id' => $departmentId,
                'document_type_id' => $docTypeId,
            ]);

            DocumentType::whereKey($docTypeId)->update(['is_universal' => false]);
        }

        return redirect()->route('admin.master.department-document-types.index')->with('success', 'Relasi berhasil ditambahkan');
    }

    public function edit(string $id)
    {
        if ($id === 'universal') {
            $department = new Department([
                'id' => 'universal',
                'name' => 'Universal (Semua Departemen)',
            ]);

            $departments = Department::orderBy('name')->get();
            $documentTypes = DocumentType::orderBy('name')->get();
            $selected = DocumentType::where('is_universal', true)->pluck('id')->toArray();

            return view('admin.master.department-document-types.edit', compact('department', 'departments', 'documentTypes', 'selected'));
        }

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
            'department_id' => 'nullable',
            'document_type_ids' => 'nullable|array',
            'document_type_ids.*' => 'required|uuid|exists:document_types,id',
        ]);

        $targetDepartmentId = $data['department_id'] ?? $id;
        $selected = $data['document_type_ids'] ?? [];

        if ($targetDepartmentId === 'universal' || $id === 'universal') {
            if (! empty($selected)) {
                DocumentType::whereIn('id', $selected)->update(['is_universal' => true]);
                DepartmentDocumentType::whereIn('document_type_id', $selected)->delete();
            }

            // Only un-set is_universal for types that are currently universal,
            // NOT in the new selection, AND not already mapped to a specific department.
            // Types mapped to departments are handled by their own department mapping.
            DocumentType::where('is_universal', true)
                ->whereNotIn('id', $selected)
                ->whereDoesntHave('departments')
                ->update(['is_universal' => false]);

            return redirect()
                ->route('admin.master.department-document-types.index')
                ->with('success', 'Relasi universal berhasil diperbarui');
        }

        $department = Department::findOrFail($targetDepartmentId);

        // sync using Eloquent many-to-many
        $department->documentTypes()->sync($selected);

        if (! empty($selected)) {
            DocumentType::whereIn('id', $selected)->update(['is_universal' => false]);
        }

        return redirect()
            ->route('admin.master.department-document-types.index')
            ->with('success', 'Relasi berhasil diperbarui');
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
