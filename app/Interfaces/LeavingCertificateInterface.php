<?php

namespace App\Interfaces;

interface LeavingCertificateInterface
{
    public function getAll(array $filters = []);
    public function findById(int $id);
    public function findByAdmission(int $admissionId);
    public function create(array $data): \App\Models\LeavingCertificate;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function checkFeesDue(int $admissionId): array;
}
