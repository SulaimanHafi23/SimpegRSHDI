<?php

namespace App\Http\Controllers\Employee;

use App\Exports\EmployeeBusinessTripExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessTrip\BusinessTripRequest;
use App\Models\BusinessTrip;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class BusinessTripController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $worker = $user->worker;
        if (!$worker) {
            return redirect()->route('employee.dashboard')->with('error', 'Data pekerja tidak ditemukan.');
        }

        $filters = [
            'worker_id' => $worker->id,
            'status' => $request->status,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'per_page' => $request->per_page ?? 15,
        ];

        $query = BusinessTrip::where('worker_id', $worker->id);
        if ($filters['status']) $query->where('status', $filters['status']);
        if ($filters['date_from']) $query->whereDate('start_date', '>=', $filters['date_from']);
        if ($filters['date_to']) $query->whereDate('end_date', '<=', $filters['date_to']);

        $businessTrips = $query->orderBy('start_date', 'desc')->paginate($filters['per_page']);

        return view('employee.business-trips.index', compact('businessTrips', 'filters', 'worker'));
    }

    public function create()
    {
        return view('employee.business-trips.create');
    }

    public function store(BusinessTripRequest $request)
    {
        $user = Auth::user();
        $worker = $user->worker;
        if (!$worker) {
            return redirect()->route('employee.dashboard')->with('error', 'Data pekerja tidak ditemukan.');
        }

        $data = $request->validated();
        $data['worker_id'] = $worker->id;
        $data['status'] = 'pending';
        $data['id'] = Str::uuid()->toString();

        if ($request->hasFile('supporting_document')) {
            $ext = $request->file('supporting_document')->getClientOriginalExtension();
            $filename = sprintf('%s_business_trip_%s.%s', $worker->id, now()->format('YmdHis'), $ext);
            $data['supporting_document_path'] = $request->file('supporting_document')
                ->storeAs('business-trip-documents', $filename, 'public');
        }

        if (($data['trip_duration_type'] ?? 'full_day') === 'half_day') {
            $data['end_date'] = $data['start_date'];
        }

        try {
            $trip = BusinessTrip::create($data);
            $this->notifyApprovers($trip);

            return redirect()->route('employee.business-trips.index')->with('success', 'Permohonan perjalanan dinas berhasil diajukan.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal membuat permohonan: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $user = Auth::user();
        $worker = $user->worker;
        $trip = BusinessTrip::findOrFail($id);

        if ($trip->worker_id !== $worker->id) {
            abort(403, 'Unauthorized');
        }

        return view('employee.business-trips.show', compact('trip', 'worker'));
    }

    public function cancel(string $id)
    {
        $user = Auth::user();
        $worker = $user->worker;
        $trip = BusinessTrip::findOrFail($id);

        if ($trip->worker_id !== $worker->id) {
            abort(403, 'Unauthorized');
        }

        if ($trip->status !== 'pending') {
            return back()->with('error', 'Hanya permohonan pending yang dapat dibatalkan.');
        }

        $trip->update(['status' => 'cancelled']);

        return redirect()->route('employee.business-trips.index')->with('success', 'Permohonan perjalanan dinas dibatalkan.');
    }

    public function export(Request $request)
    {
        $worker = Auth::user()->worker;
        if (!$worker) {
            return redirect()->route('employee.dashboard')->with('error', 'Data pekerja tidak ditemukan.');
        }

        $format = $request->input('format', 'pdf');

        $query = BusinessTrip::where('worker_id', $worker->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('end_date', '<=', $request->date_to);
        }

        $trips = $query->orderBy('start_date', 'desc')->get();
        $filters = $request->only(['date_from', 'date_to', 'status']);

        if ($format === 'excel') {
            return Excel::download(
                new EmployeeBusinessTripExport($trips, $worker),
                'perjalanan-dinas-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        if ($format === 'csv') {
            return Excel::download(
                new EmployeeBusinessTripExport($trips, $worker),
                'perjalanan-dinas-' . now()->format('Y-m-d') . '.csv',
                \Maatwebsite\Excel\Excel::CSV
            );
        }

        // PDF
        $pdf = Pdf::loadView('employee.exports.business-trip-pdf', [
            'title' => 'Laporan Perjalanan Dinas',
            'worker' => $worker,
            'trips' => $trips,
            'filters' => $filters,
        ]);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('Perjalanan_Dinas_' . $worker->name . '_' . now()->format('YmdHis') . '.pdf');
    }

    protected function notifyApprovers(BusinessTrip $trip): void
    {
        $trip->loadMissing('worker.department');

        $departmentId = $trip->worker?->department_id;

        $recipients = User::query()
            ->where('is_active', true)
            ->where(function ($query) use ($departmentId) {
                $query->whereHas('roles', function ($roleQuery) {
                    $roleQuery->whereIn('name', ['HR', 'Super Admin']);
                });

                if ($departmentId) {
                    $query->orWhere(function ($managerQuery) use ($departmentId) {
                        $managerQuery->whereHas('roles', function ($roleQuery) {
                            $roleQuery->where('name', 'Manager');
                        })->whereHas('worker', function ($workerQuery) use ($departmentId) {
                            $workerQuery->where('department_id', $departmentId);
                        });
                    });
                }
            })
            ->get()
            ->unique('id');

        foreach ($recipients as $recipient) {
            $this->notificationService->create([
                'user_id' => $recipient->id,
                'type' => 'business_trip_submitted',
                'title' => 'Pengajuan Perjalanan Dinas Baru',
                'message' => sprintf(
                    '%s mengajukan perjalanan dinas ke %s pada %s.',
                    $trip->worker->name ?? 'Pegawai',
                    $trip->destination,
                    $trip->start_date ? \Carbon\Carbon::parse($trip->start_date)->translatedFormat('d M Y') : '-'
                ),
                'data' => [
                    'business_trip_id' => $trip->id,
                    'worker_id' => $trip->worker_id,
                    'type' => 'business_trip',
                    'action' => 'submitted',
                ],
            ]);
        }
    }
}
