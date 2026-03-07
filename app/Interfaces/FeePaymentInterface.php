<?php
namespace App\Interfaces;

interface FeePaymentInterface
{
    public function getByStudent($student_user_id);
    public function store($data);
    public function findById($id);
    public function getDailyCollection($date);
    public function getByDateRange($from, $to);
    public function getDefaulters($session_id);
    public function getCategoryWiseSummary($session_id);
}
