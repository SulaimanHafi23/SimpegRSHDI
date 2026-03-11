<?php

namespace App\Http\Controllers\Promotion;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Services\Promotion\PromotionService;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function __construct(private readonly PromotionService $service)
    {
        $this->middleware('auth');
        $this->middleware('permission:promotion.manage|promotion.view');
    }

    public function index(Request $request)
    {
        $filters = [
            'search'   => $request->input('search'),
            'status'   => $request->input('status'),
            'per_page' => $request->input('per_page', 15),
        ];

        $requests = $this->service->getAll($filters);

        return view('admin.promotions.index', compact('requests', 'filters'));
    }

    public function create()
    {
        $this->authorizePermission('promotion.manage');

        $workers = Worker::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'nip', 'name', 'rank', 'rank_level', 'base_salary', 'payroll_category']);

        return view('admin.promotions.create', compact('workers'));
    }

    public function store(Request $request)
    {
        $this->authorizePermission('promotion.manage');

        $validated = $request->validate([
            'worker_id'            => 'required|exists:workers,id',
            'promotion_type'       => 'required|string|max:50',
            'proposed_rank'        => 'required|string|max:100',
            'proposed_rank_level'  => 'nullable|string|max:50',
            'proposed_base_salary' => 'required|numeric|min:0',
            'effective_date'       => 'required|date',
            'reason'               => 'nullable|string|max:1000',
        ]);

        try {
            $promotion = $this->service->create($validated);

            return redirect()
                ->route('admin.promotions.show', $promotion->id)
                ->with('success', 'Pengajuan kenaikan pangkat berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $request  = $this->service->getById($id);
        $histories = $request->worker?->promotionHistories()->with('approvedBy')->latest('effective_date')->get() ?? collect();

        return view('admin.promotions.show', compact('request', 'histories'));
    }

    public function approve(Request $request, string $id)
    {
        $this->authorizePermission('promotion.manage');

        $request->validate(['notes' => 'nullable|string|max:500']);

        try {
            $this->service->approve($this->service->getById($id), $request->input('notes'));

            return redirect()
                ->route('admin.promotions.show', $id)
                ->with('success', 'Pengajuan kenaikan pangkat disetujui. Data pegawai telah diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, string $id)
    {
        $this->authorizePermission('promotion.manage');

        $request->validate(['rejection_reason' => 'required|string|max:500']);

        try {
            $this->service->reject($this->service->getById($id), $request->input('rejection_reason'));

            return redirect()
                ->route('admin.promotions.show', $id)
                ->with('success', 'Pengajuan kenaikan pangkat ditolak.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    protected function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403);
    }
}
