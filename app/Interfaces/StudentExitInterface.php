<?php

namespace App\Interfaces;

interface StudentExitInterface
{
    public function getAll();
    public function getById($id);
    public function getByAdmissionId($admissionId);
    public function store(array $data);
    public function update($id, array $data);
}
