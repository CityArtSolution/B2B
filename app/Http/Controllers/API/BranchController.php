<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BranchController extends Controller
{
    /**
     * Get all active branches.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $branches = Branch::where('status', 1)->get();
        
        return $this->json('branches', [
            'branches' => $branches
        ]);
    }

    /**
     * Store selected branch in session.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function selectBranch(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id'
        ]);

        $branch = Branch::findOrFail($request->branch_id);
        $user = auth()->user();

        // Store in session for web users
        session(['selected_branch' => $branch->id]);

        // Also store in cache for API users (using user ID as key)
        if ($user) {
            Cache::put('selected_branch_' . $user->id, $branch->id, now()->addDays(7));
        }

        // Debug logging
        \Log::info('Branch selected', [
            'branch_id' => $branch->id,
            'user_id' => $user ? $user->id : null,
            'session_id' => session()->getId(),
            'selected_branch_in_session' => session('selected_branch'),
            'selected_branch_in_cache' => $user ? Cache::get('selected_branch_' . $user->id) : null
        ]);

        return $this->json('Branch selected successfully', [
            'branch' => $branch
        ]);
    }

    /**
     * Get currently selected branch.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSelectedBranch()
    {
        $user = auth()->user();
        $branchId = session('selected_branch');

        // If no session branch, check cache for API users
        if (!$branchId && $user) {
            $branchId = Cache::get('selected_branch_' . $user->id);
        }

        // Debug logging
        \Log::info('Get selected branch', [
            'branch_id' => $branchId,
            'user_id' => $user ? $user->id : null,
            'session_id' => session()->getId(),
            'selected_branch_in_session' => session('selected_branch'),
            'selected_branch_in_cache' => $user ? Cache::get('selected_branch_' . $user->id) : null
        ]);

        if ($branchId) {
            $branch = Branch::find($branchId);
            return $this->json('Selected branch', [
                'branch' => $branch
            ]);
        }

        return $this->json('No branch selected', [
            'branch' => null
        ]);
    }
}
