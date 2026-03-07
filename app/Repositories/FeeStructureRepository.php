<?php
namespace App\Repositories;

use App\Models\FeeStructure;
use App\Interfaces\FeeStructureInterface;

class FeeStructureRepository implements FeeStructureInterface
{
    public function getAll($session_id)
    {
        return FeeStructure::with('schoolClass')
            ->where('session_id', $session_id)
            ->orderBy('class_id')
            ->orderBy('fee_category')
            ->get();
    }

    public function getByClass($class_id, $academic_year)
    {
        return FeeStructure::with('schoolClass')
            ->where('class_id', $class_id)
            ->where('academic_year', $academic_year)
            ->get();
    }

    public function getByClassAndCategory($class_id, $academic_year, $fee_category)
    {
        return FeeStructure::where('class_id', $class_id)
            ->where('academic_year', $academic_year)
            ->where('fee_category', $fee_category)
            ->first();
    }

    public function store($data)
    {
        return FeeStructure::updateOrCreate(
            [
                'class_id'     => $data['class_id'],
                'academic_year'=> $data['academic_year'],
                'fee_category' => $data['fee_category'],
            ],
            [
                'session_id'   => $data['session_id'],
                'admission_fee'=> $data['admission_fee'] ?? 0,
                'tuition_fee'  => $data['tuition_fee']   ?? 0,
                'transport_fee'=> $data['transport_fee'] ?? 0,
                'other_fee'    => $data['other_fee']     ?? 0,
            ]
        );
    }

    public function update($id, $data)
    {
        $feeStructure = FeeStructure::findOrFail($id);
        $feeStructure->update($data);
        return $feeStructure;
    }

    public function delete($id)
    {
        FeeStructure::findOrFail($id)->delete();
    }
}
