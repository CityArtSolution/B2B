<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InstallmentRequest;
use App\Models\Installment;
use App\Repositories\InstallmentRepository;

class InstallmentController extends Controller
{
    public function index()
    {
        $installments = Installment::paginate(10);
        return view('admin.installment.index', compact('installments'));
    }

    public function create()
    {
        return view('admin.installment.create');
    }

    public function store(InstallmentRequest $request)
    {
        InstallmentRepository::storeByRequest($request);
        return to_route('admin.installment.index')->withSuccess(__('Installment created successfully'));
    }

    public function edit(Installment $installment)
    {
        return view('admin.installment.edit', compact('installment'));
    }

    public function update(InstallmentRequest $request, Installment $installment)
    {
        InstallmentRepository::updateByRequest($request, $installment);
        return to_route('admin.installment.index')->withSuccess(__('Installment updated successfully'));
    }

    public function destroy(Installment $installment)
    {
        $installment->delete();
        return to_route('admin.installment.index')->withSuccess(__('Installment deleted successfully'));
    }
}
