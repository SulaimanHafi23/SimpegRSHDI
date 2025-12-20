# 🚀 Component Cheat Sheet - Quick Reference

## 📑 Table of Contents
- [Page Structure](#page-structure)
- [Forms](#forms)
- [Tables](#tables)
- [Buttons & Actions](#buttons--actions)
- [Feedback](#feedback)
- [Common Patterns](#common-patterns)

---

## Page Structure

### Page Header
```blade
<x-page-header title="Title" description="Description" icon="fas fa-icon">
    <x-slot:actions>
        <x-button variant="primary">Action</x-button>
    </x-slot:actions>
</x-page-header>
```

### Card Container
```blade
<x-card title="Title">
    Content here
</x-card>

<!-- No padding for tables -->
<x-card title="Title" :no-padding="true">
    <x-table>...</x-table>
</x-card>
```

### Stats Cards (Dashboard)
```blade
<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <x-stats-card title="Total" value="150" icon="fas fa-users" color="blue" />
    <x-stats-card title="Active" value="120" icon="fas fa-check" color="green" />
    <x-stats-card title="Pending" value="25" icon="fas fa-clock" color="yellow" />
    <x-stats-card title="Inactive" value="5" icon="fas fa-ban" color="red" />
</div>
```

---

## Forms

### Basic Form Structure
```blade
<form action="{{ route('...') }}" method="POST">
    @csrf
    <x-card title="Form Title">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Form fields here -->
        </div>
    </x-card>
</form>
```

### Text Input
```blade
<x-form.input 
    name="name" 
    label="Full Name" 
    placeholder="Enter name"
    required 
    help="Helper text" />
```

### Email/Number/Date Input
```blade
<x-form.input name="email" type="email" label="Email" required />
<x-form.input name="age" type="number" label="Age" />
<x-form.input name="birth_date" type="date" label="Birth Date" />
```

### Select Dropdown
```blade
<!-- With options array -->
<x-form.select 
    name="status" 
    label="Status"
    :options="['active' => 'Active', 'inactive' => 'Inactive']"
    selected="active"
    required />

<!-- With manual options -->
<x-form.select name="department" label="Department" required>
    @foreach($departments as $dept)
        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
    @endforeach
</x-form.select>
```

### Textarea
```blade
<x-form.textarea 
    name="description" 
    label="Description" 
    rows="5"
    placeholder="Enter description" />
```

### Checkbox
```blade
<x-form.checkbox 
    name="agree" 
    label="I agree to terms and conditions"
    help="You must agree to continue" />
```

### File Upload
```blade
<x-form.file 
    name="photo" 
    label="Photo"
    accept="image/*"
    preview
    help="Max 2MB, JPG/PNG only" />
```

### Filter Section
```blade
<x-filter-section action="{{ route('...index') }}">
    <x-form.input name="search" label="Search" :value="request('search')" />
    <x-form.select name="status" label="Status" :selected="request('status')">
        <option value="">All</option>
        <option value="active">Active</option>
    </x-form.select>
</x-filter-section>
```

---

## Tables

### Basic Table
```blade
<x-card title="Data List" :no-padding="true">
    @if($items->count() > 0)
        <x-table responsive striped hover>
            <x-slot:thead>
                <tr>
                    <x-table.cell header>No</x-table.cell>
                    <x-table.cell header>Name</x-table.cell>
                    <x-table.cell header>Actions</x-table.cell>
                </tr>
            </x-slot:thead>

            @foreach($items as $index => $item)
                <x-table.row>
                    <x-table.cell>{{ $index + 1 }}</x-table.cell>
                    <x-table.cell>{{ $item->name }}</x-table.cell>
                    <x-table.cell>
                        <!-- Actions -->
                    </x-table.cell>
                </x-table.row>
            @endforeach
        </x-table>

        <x-slot:cardFooter>
            <x-pagination :paginator="$items" />
        </x-slot:cardFooter>
    @else
        <x-empty-state 
            title="No data"
            description="No records found"
            actionText="Add New"
            :actionUrl="route('...create')" />
    @endif
</x-card>
```

### Table with Image
```blade
<x-table.cell>
    @if($item->photo)
        <img src="{{ asset('storage/' . $item->photo) }}" 
             class="w-10 h-10 rounded-full object-cover">
    @else
        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
            <i class="fas fa-user text-gray-400"></i>
        </div>
    @endif
</x-table.cell>
```

### Table with Dropdown Actions
```blade
<x-table.cell>
    <x-dropdown align="right" width="48">
        <x-slot:trigger>
            <x-button variant="outline-secondary" size="sm" icon="fas fa-ellipsis-v" />
        </x-slot:trigger>

        <x-dropdown.item icon="fas fa-eye" :href="route('...show', $item->id)">
            Detail
        </x-dropdown.item>
        <x-dropdown.item icon="fas fa-edit" :href="route('...edit', $item->id)">
            Edit
        </x-dropdown.item>
        <x-dropdown.divider />
        <x-dropdown.item icon="fas fa-trash" onclick="confirmDelete({{ $item->id }})">
            Delete
        </x-dropdown.item>
    </x-dropdown>
</x-table.cell>
```

---

## Buttons & Actions

### Button Variants
```blade
<x-button variant="primary" icon="fas fa-save">Save</x-button>
<x-button variant="success" icon="fas fa-plus">Create</x-button>
<x-button variant="danger" icon="fas fa-trash">Delete</x-button>
<x-button variant="warning" icon="fas fa-exclamation">Warning</x-button>
<x-button variant="secondary" icon="fas fa-times">Cancel</x-button>

<!-- Outline variants -->
<x-button variant="outline-primary">Outline</x-button>
<x-button variant="outline-danger">Outline Danger</x-button>
```

### Button Sizes
```blade
<x-button size="xs">Extra Small</x-button>
<x-button size="sm">Small</x-button>
<x-button size="md">Medium</x-button> <!-- Default -->
<x-button size="lg">Large</x-button>
<x-button size="xl">Extra Large</x-button>
```

### Button with Loading
```blade
<x-button variant="primary" :loading="true">
    Saving...
</x-button>
```

### Form Action Buttons
```blade
<div class="flex justify-end gap-3">
    <x-button 
        type="button"
        variant="outline-secondary" 
        onclick="window.history.back()">
        Cancel
    </x-button>
    <x-button type="submit" variant="success" icon="fas fa-save">
        Save
    </x-button>
</div>
```

---

## Feedback

### Alerts
```blade
@if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif

@if(session('error'))
    <x-alert type="error">{{ session('error') }}</x-alert>
@endif

@if($errors->any())
    <x-alert type="error">
        <strong>{{ $errors->count() }} errors found:</strong>
        <ul class="mt-2 ml-4 list-disc">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif
```

### Badges (Status)
```blade
@if($item->status === 'active')
    <x-badge variant="success" icon="fas fa-check-circle">Active</x-badge>
@elseif($item->status === 'pending')
    <x-badge variant="warning" icon="fas fa-clock">Pending</x-badge>
@else
    <x-badge variant="danger" icon="fas fa-times-circle">Inactive</x-badge>
@endif
```

### Loading State
```blade
<x-loading size="lg" color="blue" text="Loading data..." />
```

### Empty State
```blade
<x-empty-state 
    icon="fas fa-inbox" 
    title="No data found"
    description="There are no records available"
    actionText="Add New Record"
    :actionUrl="route('...create')" />
```

---

## Common Patterns

### Index Page Pattern
```blade
@extends('layouts.admin')

@section('title', 'Page Title')

@section('content')
<div class="space-y-6">
    <x-page-header title="..." description="...">
        <x-slot:actions>
            <x-button variant="success" onclick="...">Add</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-alert type="success">...</x-alert>

    <x-filter-section action="...">...</x-filter-section>

    <x-card title="..." :no-padding="true">
        <x-table>...</x-table>
        <x-slot:cardFooter>
            <x-pagination :paginator="$items" />
        </x-slot:cardFooter>
    </x-card>
</div>
@endsection
```

### Create/Edit Form Pattern
```blade
@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <x-page-header title="...">
        <x-slot:actions>
            <x-button variant="outline-secondary" onclick="window.history.back()">
                Back
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <x-alert type="error">...</x-alert>

    <form action="..." method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($item)) @method('PUT') @endif

        <x-card title="Section 1">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input name="..." label="..." required />
            </div>
        </x-card>

        <x-card>
            <div class="flex justify-end gap-3">
                <x-button type="button" variant="outline-secondary" 
                    onclick="window.history.back()">Cancel</x-button>
                <x-button type="submit" variant="success">Save</x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection
```

### Modal Pattern
```blade
{{-- Button to open modal --}}
<x-button @click="$dispatch('open-modal-confirm')">
    Open Modal
</x-button>

{{-- Modal definition --}}
<x-modal name="confirm" title="Confirmation" size="sm">
    <p>Are you sure?</p>
    
    <x-slot:footer>
        <x-button @click="$dispatch('close-modal-confirm')">Cancel</x-button>
        <x-button variant="danger">Confirm</x-button>
    </x-slot:footer>
</x-modal>
```

### Delete Confirmation Pattern
```blade
{{-- In table action --}}
<x-dropdown.item 
    icon="fas fa-trash" 
    onclick="confirmDelete({{ $item->id }})">
    Delete
</x-dropdown.item>

{{-- Modal --}}
<x-modal name="delete-item" title="Confirm Delete" size="sm">
    <p>Are you sure you want to delete this item?</p>
    
    <x-slot:footer>
        <x-button @click="$dispatch('close-modal-delete-item')">Cancel</x-button>
        <form id="delete-form" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <x-button type="submit" variant="danger">Delete</x-button>
        </form>
    </x-slot:footer>
</x-modal>

{{-- JavaScript --}}
@push('scripts')
<script>
function confirmDelete(id) {
    document.getElementById('delete-form').action = `/admin/items/${id}`;
    window.dispatchEvent(new CustomEvent('open-modal-delete-item'));
}
</script>
@endpush
```

---

## 💡 Pro Tips

### 1. Consistent Spacing
```blade
<div class="space-y-6">  <!-- Use for vertical spacing -->
    <x-component />
    <x-component />
</div>

<div class="flex gap-3">  <!-- Use for horizontal spacing -->
    <x-button />
    <x-button />
</div>
```

### 2. Grid Layouts
```blade
<!-- Responsive grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <!-- Items -->
</div>

<!-- Form grid -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <x-form.input ... />
    <x-form.input ... />
</div>
```

### 3. Permission Checks
```blade
@can('edit-users')
    <x-button variant="primary">Edit</x-button>
@endcan
```

### 4. Conditional Rendering
```blade
@if($items->count() > 0)
    <x-table>...</x-table>
@else
    <x-empty-state ... />
@endif
```

---

## 📚 References

- [Full Documentation](./COMPONENTS-GUIDE.md)
- [Migration Guide](./MIGRATION-GUIDE.md)
- [Examples](./resources/views/examples/)

---

**Print this cheat sheet for quick reference! 📄**
