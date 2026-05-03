@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="space-y-6">
	<x-page-header title="Edit User" description="Perbarui data akun pengguna" icon="fas fa-user-edit" />


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
		<form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
			@csrf
			@method('PUT')

			<div class="grid grid-cols-1 gap-4">
				<div class="flex items-center">
					<input type="hidden" name="is_active" value="0">
					<input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="rounded">
					<label class="ml-2 text-sm font-medium text-gray-700">Akun Aktif</label>
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700">Pekerja (Worker)</label>
					<select name="worker_id" class="mt-1 block w-full border rounded px-3 py-2">
						<option value="">-- Pilih Pegawai --</option>
						@foreach($workers as $w)
							<option value="{{ $w->id }}" {{ old('worker_id', $user->worker_id) == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->nip }})</option>
						@endforeach
					</select>
					@error('worker_id') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700">Email</label>
					<input type="email" name="email" value="{{ old('email', $user->email) }}" class="mt-1 block w-full border rounded px-3 py-2" required>
					@error('email') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700">Username</label>
					<input type="text" name="username" value="{{ old('username', $user->username) }}" class="mt-1 block w-full border rounded px-3 py-2" required>
					@error('username') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700">Password (kosongkan jika tidak diubah)</label>
					<input type="password" name="password" class="mt-1 block w-full border rounded px-3 py-2">
					@error('password') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
					<input type="password" name="password_confirmation" class="mt-1 block w-full border rounded px-3 py-2">
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700">Foto (opsional)</label>
					<input type="file" name="photo" class="mt-1 block w-full">
					@error('photo') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700">Roles</label>
					<input type="text" id="edit-roles-search" placeholder="Cari role..." class="mt-1 block w-full border rounded px-3 py-2" />
					<select name="roles[]" id="edit-roles" multiple class="mt-2 block w-full border rounded px-3 py-2" size="6">
						@foreach($roles as $role)
							<option value="{{ $role->id }}" {{ in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $role->name }}</option>
						@endforeach
					</select>
					@error('roles') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
				</div>

				<div class="flex items-center justify-end space-x-2">
					<a href="{{ route('admin.users.index') }}" class="px-4 py-2 border rounded">Batal</a>
					<button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Simpan</button>
				</div>
			</div>
		</form>
	</x-card>
</div>
@endsection

			@push('scripts')
			<script>
			document.addEventListener('DOMContentLoaded', function () {
				const search = document.getElementById('edit-roles-search');
				const select = document.getElementById('edit-roles');
				if (search && select) {
					search.addEventListener('input', function () {
						const q = this.value.toLowerCase();
						Array.from(select.options).forEach(opt => {
							const text = opt.text.toLowerCase();
							opt.hidden = q.length > 0 ? !text.includes(q) : false;
						});
					});
				}
			});
			</script>
			@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
	const form = document.querySelector('form');
	if (form) {
		form.addEventListener('submit', function (e) {
			const fileInput = document.querySelector('input[type="file"][name="photo"]');
			if (fileInput && fileInput.files && fileInput.files.length > 0) {
				const maxBytes = 10 * 1024 * 1024; // 10 MB
				if (fileInput.files[0].size > maxBytes) {
					e.preventDefault();
					window.showWarningAlert('Validasi', 'Ukuran file terlalu besar. Maks 10 MB.');
					return false;
				}
			}
		});
	}
});
</script>
@endpush
