@extends('layouts.admin')

@section('title', 'Tambah Pegawai')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header 
        title="Tambah Pegawai" 
        description="Form untuk menambahkan pegawai baru"
        icon="fas fa-user-plus">
        <x-slot:actions>
            <x-button 
                variant="outline-secondary" 
                icon="fas fa-arrow-left"
                onclick="window.location.href='{{ route('admin.workers.index') }}'">
                Kembali
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Error Alert --}}
    @if($errors->any())
        <x-alert type="error">
            <strong>Terdapat {{ $errors->count() }} kesalahan:</strong>
            <ul class="mt-2 ml-4 list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <form action="{{ route('admin.workers.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Personal Information --}}
        <x-card title="Informasi Personal" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input 
                    name="nip" 
                    label="NIP" 
                    placeholder="Masukkan NIP"
                    required />

                <x-form.input 
                    name="name" 
                    label="Nama Lengkap" 
                    placeholder="Masukkan nama lengkap"
                    required />

                <x-form.input 
                    name="email" 
                    type="email"
                    label="Email" 
                    placeholder="email@example.com"
                    required />

                <x-form.input 
                    name="phone" 
                    label="No. Telepon" 
                    placeholder="08xxxxxxxxxx"
                    required />

                <x-form.input 
                    name="birth_date" 
                    type="date"
                    label="Tanggal Lahir" 
                    required />

                <x-form.input 
                    name="birth_place" 
                    label="Tempat Lahir" 
                    placeholder="Masukkan tempat lahir"
                    required />

                <x-form.select 
                    name="gender_id" 
                    label="Jenis Kelamin"
                    required>
                    @foreach($genders as $gender)
                        <option value="{{ $gender->id }}">{{ $gender->name }}</option>
                    @endforeach
                </x-form.select>

                <x-form.select 
                    name="religion_id" 
                    label="Agama"
                    required>
                    @foreach($religions as $religion)
                        <option value="{{ $religion->id }}">{{ $religion->name }}</option>
                    @endforeach
                </x-form.select>

                <div class="md:col-span-2">
                    <x-form.textarea 
                        name="address" 
                        label="Alamat" 
                        placeholder="Masukkan alamat lengkap"
                        rows="3"
                        required />
                </div>

                <x-form.file 
                    name="photo" 
                    label="Foto" 
                    accept="image/*"
                    preview
                    help="Maksimal 2MB, format: JPG, PNG" />
            </div>
        </x-card>

        {{-- Employment Information --}}
        <x-card title="Informasi Kepegawaian" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.select 
                    name="department_id" 
                    label="Departemen"
                    required>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </x-form.select>

                <x-form.select 
                    name="location_id" 
                    label="Lokasi"
                    required>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                </x-form.select>

                <x-form.input 
                    name="join_date" 
                    type="date"
                    label="Tanggal Bergabung" 
                    required />

                <x-form.select 
                    name="employment_status" 
                    label="Status Kepegawaian"
                    :options="[
                        'permanent' => 'Tetap',
                        'contract' => 'Kontrak',
                        'intern' => 'Magang'
                    ]"
                    required />

                <div class="md:col-span-2">
                    <x-form.checkbox 
                        name="create_user_account" 
                        label="Buat Akun User"
                        help="Centang jika ingin membuat akun login untuk pegawai ini" />
                </div>
            </div>
        </x-card>

        {{-- Form Actions --}}
        <x-card>
            <div class="flex justify-end gap-3">
                <x-button 
                    type="button"
                    variant="outline-secondary" 
                    icon="fas fa-times"
                    onclick="window.location.href='{{ route('admin.workers.index') }}'">
                    Batal
                </x-button>
                <x-button 
                    type="submit"
                    variant="success" 
                    icon="fas fa-save">
                    Simpan
                </x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection
