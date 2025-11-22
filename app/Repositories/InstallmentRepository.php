<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\Installment;

class InstallmentRepository extends Repository
{
    public static function model()
    {
        return Installment::class;    
    }

    public static function storeByRequest($request): Installment
    {
        return Installment::create($request->only('name','value'));
    }

    public static function updateByRequest($request, Installment $installment): Installment
    {
        $installment->update($request->only('name','value'));
        return $installment;
    }
}