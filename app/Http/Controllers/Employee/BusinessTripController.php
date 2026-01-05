<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessTrip\BusinessTripRequest;
use App\Models\BusinessTrip;
use Illuminate\Http\Request;

class BusinessTripController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
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
        $user = auth()->user();
        $worker = $user->worker;
        if (!$worker) {
            return redirect()->route('employee.dashboard')->with('error', 'Data pekerja tidak ditemukan.');
        }

        $data = $request->validated();
        $data['worker_id'] = $worker->id;
        $data['status'] = 'pending';
        $data['id'] = \Str::uuid()->toString();

        try {
            BusinessTrip::create($data);
            return redirect()->route('employee.business-trips.index')->with('success', 'Permohonan perjalanan dinas berhasil diajukan.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal membuat permohonan: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $user = auth()->user();
        $worker = $user->worker;
        $trip = BusinessTrip::findOrFail($id);

        if ($trip->worker_id !== $worker->id) {
            abort(403, 'Unauthorized');
        }

        return view('employee.business-trips.show', compact('trip', 'worker'));
    }

    public function cancel(string $id)
    {
        $user = auth()->user();
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
}
