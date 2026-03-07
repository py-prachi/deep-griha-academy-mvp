<?php
namespace App\Interfaces;

interface FeeStructureInterface
{
    public function getAll($session_id);
    public function getByClass($class_id, $academic_year);
    public function getByClassAndCategory($class_id, $academic_year, $fee_category);
    public function store($data);
    public function update($id, $data);
    public function delete($id);
}
