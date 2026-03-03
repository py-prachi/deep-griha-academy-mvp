<?php

namespace App\Interfaces;

interface AdmissionInterface
{
    public function getAll($filters = []);
    public function findById($id);
    public function create($data);
    public function update($id, $data);
    public function updateStatus($id, $status, $reason = null);
    public function confirm($id, $data);
    public function cancel($id, $reason);
    public function getCancelled();
    public function getByStatus($status);
}
