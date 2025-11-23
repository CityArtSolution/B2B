<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

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

        // Store in session
        session(['selected_branch' => $branch->id]);

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
        $branchId = session('selected_branch');

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
