<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\PromotionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromotionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:promotion.view');
    }

    public function index(Request $request)
    {
        $worker = Auth::user()?->worker;

        if (!$worker) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Data pegawai tidak ditemukan.');
        }

        $promotions = PromotionRequest::query()
            ->where('worker_id', $worker->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('employee.promotions.index', compact('promotions', 'worker'));
    }

    public function show(string $id)
    {
        $worker = Auth::user()?->worker;

        if (!$worker) {
            abort(403);
        }

        $promotion = PromotionRequest::where('worker_id', $worker->id)
            ->with(['reviewer'])
            ->findOrFail($id);

        return view('employee.promotions.show', compact('promotion', 'worker'));
    }
}
