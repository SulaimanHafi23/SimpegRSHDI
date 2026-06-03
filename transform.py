import re

with open('c:/laragon/www/SimpegRSHDI/resources/views/admin/leave/show.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace variable
content = content.replace('$leaveRequest', '$leave')

# Replace form action for reject
content = content.replace('admin.leave.reject', 'approvals.leaves.reject')

# Replace approval actions block
approval_actions = '''            {{-- Approval Actions --}}
            @if($normalizedStatus === 'pending' && $isManager)
                <div class="flex gap-3">
                    <form action="{{ route('approvals.leaves.verify', $leave->id) }}" method="POST" class="inline">
                        @csrf
                        <x-button
                            type="submit"
                            variant="success"
                            icon="fas fa-check-double"
                            onclick="event.preventDefault(); showConfirmAlert('Verifikasi Pengajuan?', 'Anda yakin ingin memverifikasi pengajuan cuti ini? Pengajuan akan diteruskan ke HR.', () => this.closest('form').submit());">
                            Verifikasi
                        </x-button>
                    </form>
                    <x-button
                        type="button"
                        variant="danger"
                        icon="fas fa-times"
                        onclick="openLeaveRejectModal()">
                        Tolak
                    </x-button>
                </div>
            @elseif($normalizedStatus === 'manager_verified')
                <div class="text-sm font-medium text-blue-700 bg-blue-50 px-4 py-2 rounded-lg border border-blue-200">
                    <i class="fas fa-info-circle mr-2"></i>
                    Pengajuan ini telah Anda verifikasi dan sekarang menunggu persetujuan akhir dari HR.
                </div>
            @endif'''

content = re.sub(r'\{\{-- Approval Actions --\}\}.*?@endif', approval_actions, content, flags=re.DOTALL)

# Add isManager check
is_manager_php = '''@php
            $user = Auth::user();
            $isManager = !$user->hasRole(['Super Admin', 'Admin', 'admin', 'HR', 'hr']) && $user->hasRole(['Manager', 'manager']);
        @endphp
        
        <div class="flex items-center justify-between">'''
content = content.replace('<div class="flex items-center justify-between">', is_manager_php, 1)

# Modify status config labels to match manager context
content = content.replace("'pending' => ['variant' => 'warning', 'icon' => 'fas fa-clock', 'label' => 'Menunggu Persetujuan'],", "'pending' => ['variant' => 'warning', 'icon' => 'fas fa-clock', 'label' => 'Menunggu Verifikasi Manager'],\n                        'manager_verified' => ['variant' => 'info', 'icon' => 'fas fa-user-check', 'label' => 'Telah Diverifikasi Manager'],")

# Remove delete button logic since manager shouldn't delete leaves (only HR/Admin)
content = re.sub(r'@if\(\$normalizedStatus === \'pending\'\)\s*@can\(\'delete-leave\'\).*?@endcan\s*@endif', '', content, flags=re.DOTALL)

# Fix back button if any (in HR view, it might be breadcrumbs. Manager view has breadcrumbs)
# Manager view should have a link back to approvals.leaves.index
content = content.replace("route('admin.leave.index')", "route('approvals.leaves.index')")

# Write to approvals/leaves/show.blade.php
with open('c:/laragon/www/SimpegRSHDI/resources/views/approvals/leaves/show.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
