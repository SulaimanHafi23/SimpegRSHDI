@extends('layouts.admin')

@section('title', 'Tambah User')

@section('content')
<div class="space-y-6">
	<x-page-header title="Tambah User" description="Buat akun pengguna baru" icon="fas fa-user-plus" />

	@if(session('error'))
		<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
			<span class="block sm:inline">{{ session('error') }}</span>
		</div>
	@endif

	@if($errors->any())
		<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
			<div class="flex items-start">
				<svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
					<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
				</svg>
				<div>
					<strong class="font-bold">Terdapat kesalahan pada form!</strong>
					<ul class="mt-2 ml-4 list-disc list-inside text-sm">
						@foreach($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			</div>
		</div>
	@endif

	<x-card>
		<form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
			@csrf

			<div class="grid grid-cols-1 gap-4">
				<div>
					<label class="block text-sm font-medium text-gray-700">Pekerja (Worker)</label>
					<select name="worker_id" class="mt-1 block w-full border rounded px-3 py-2">
						<option value="">-- Pilih Pegawai --</option>
						@foreach($workers as $w)
							<option value="{{ $w->id }}" {{ old('worker_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->nip }})</option>
						@endforeach
					</select>
					@error('worker_id') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700">Email</label>
					<input type="email" name="email" value="{{ old('email') }}" class="mt-1 block w-full border rounded px-3 py-2" required>
					@error('email') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700">Username</label>
					<input type="text" name="username" value="{{ old('username') }}" class="mt-1 block w-full border rounded px-3 py-2" required>
					@error('username') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700">Password</label>
					<input type="password" name="password" class="mt-1 block w-full border rounded px-3 py-2" required>
					@error('password') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
					<input type="password" name="password_confirmation" class="mt-1 block w-full border rounded px-3 py-2" required>
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700">Foto (opsional)</label>
					<input type="file" name="photo" class="mt-1 block w-full">
					@error('photo') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700">Roles</label>
					<input type="text" id="create-roles-search" placeholder="Cari role..." class="mt-1 block w-full border rounded px-3 py-2" />
					<select name="roles[]" id="create-roles" multiple class="mt-2 block w-full border rounded px-3 py-2" size="6">
						@foreach($roles as $role)
							<option value="{{ $role->id }}" {{ in_array($role->id, old('roles', [])) ? 'selected' : '' }}>{{ $role->name }}</option>
						@endforeach
					</select>
					@error('roles') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
				</div>

				<div class="flex items-center justify-end space-x-2">
					<a href="{{ route('admin.users.index') }}" class="px-4 py-2 border rounded">Batal</a>
					<button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Buat</button>
				</div>
			</div>
		</form>
	</x-card>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
	const search = document.getElementById('create-roles-search');
	const select = document.getElementById('create-roles');
	if (search && select) {
		search.addEventListener('input', function () {
			const q = this.value.toLowerCase();
			Array.from(select.options).forEach(opt => {
				const text = opt.text.toLowerCase();
				opt.hidden = q.length > 0 ? !text.includes(q) : false;
			});
		});
	}
	// Client-side file size check (10 MB)
	const form = document.querySelector('form');
	if (form) {
		form.addEventListener('submit', function (e) {
			const fileInput = document.querySelector('input[type="file"][name="photo"]');
			if (fileInput && fileInput.files && fileInput.files.length > 0) {
				const maxBytes = 10 * 1024 * 1024; // 10 MB
				if (fileInput.files[0].size > maxBytes) {
					e.preventDefault();
					alert('Ukuran file terlalu besar. Maks 10 MB.');
					return false;
				}
			}
		});
	}
});
</script>
@endpush
