<?php

namespace App\Repositories;

use App\Interfaces\StudentExitInterface;
use App\Models\StudentExit;
use App\Models\Admission;

class StudentExitRepository implements StudentExitInterface
{
    public function getAll()
    {
        return StudentExit::with(['admission.schoolClass', 'admission.section'])
            ->orderBy('exit_date', 'desc')
            ->get();
    }

    public function getById($id)
    {
        return StudentExit::with(['admission.schoolClass', 'admission.section'])
            ->findOrFail($id);
    }

    public function getByAdmissionId($admissionId)
    {
        return StudentExit::where('admission_id', $admissionId)->first();
    }

    public function store(array $data)
    {
        return StudentExit::create($data);
    }

    public function update($id, array $data)
    {
        $exit = StudentExit::findOrFail($id);
        $exit->update($data);
        return $exit;
    }

    /**
     * Mark the admission as exited and set exit_date on admissions table.
     * Called from controller after storing exit form.
     */
    public function markAdmissionExited(Admission $admission, $exitDate)
    {
        $admission->status    = Admission::STATUS_EXITED;
        $admission->exit_date = $exitDate;
        $admission->save();
    }
}
