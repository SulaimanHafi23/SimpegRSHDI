@extends('layouts.admin')

@section('title', 'Detail User')

@section('content')
<div class="space-y-6">
	<x-page-header title="Detail User" description="Informasi akun pengguna" icon="fas fa-user" />

	<x-card>
		<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
			<div class="col-span-1">
				<div class="flex items-center space-x-4">
					<div class="h-24 w-24 rounded-full overflow-hidden bg-gray-100">
						@if($user->worker && $user->worker->photo_url)
							<img src="{{ Storage::url($user->worker->photo_url) }}" alt="{{ $user->worker->name }}" class="h-24 w-24 object-cover">
						@else
							<div class="h-24 w-24 flex items-center justify-center text-2xl text-gray-500">{{ strtoupper(substr($user->username ?? ($user->worker->name ?? 'U'), 0, 1)) }}</div>
						@endif
					</div>
					<div>
						<h3 class="text-lg font-semibold">{{ $user->username }}</h3>
						<div class="text-sm text-gray-500">{{ $user->email }}</div>
						@if($user->worker)
							<div class="text-sm text-gray-600">{{ $user->worker->name }} ({{ $user->worker->nip }})</div>
						@endif
					</div>
				</div>
			</div>

			<div class="col-span-2">
				<div class="grid grid-cols-1 gap-3">
					<div>
						<label class="block text-sm font-medium text-gray-700">Roles</label>
						<div class="mt-1">
							@forelse($user->roles as $role)
								<x-badge class="mr-2">{{ $role->name }}</x-badge>
							@empty
								<div class="text-sm text-gray-500">Belum ada role.</div>
							@endforelse
						</div>
					</div>

					<div>
						<label class="block text-sm font-medium text-gray-700">Status Akun</label>
						<div class="mt-1">
							<x-badge :variant="$user->is_active ? 'success' : 'danger'">{{ $user->is_active ? 'Aktif' : 'Non-Aktif' }}</x-badge>
						</div>
					</div>

					<div>
						<label class="block text-sm font-medium text-gray-700">Last Login</label>
						<div class="mt-1 text-sm text-gray-600">{{ $user->last_login ? $user->last_login->format('d M Y H:i') : '-' }}</div>
					</div>
				</div>
			</div>
		</div>

		<div class="mt-6 flex justify-end space-x-2">
			<a href="{{ route('admin.users.edit', $user->id) }}" class="px-4 py-2 bg-yellow-600 text-white rounded">Edit</a>
			<a href="{{ route('admin.users.index') }}" class="px-4 py-2 border rounded">Kembali</a>
		</div>
	</x-card>
</div>
@endsection
