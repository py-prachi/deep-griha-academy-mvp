<?php

namespace App\Repositories;

use App\Models\Notice;

class NoticeRepository {
    public function store($data)
{
    return Notice::create([
        'title' => $data['title'],
        'description' => $data['description'],
        'session_id' => $data['session_id'],
        'class_id' => $data['class_id'] ?? null, // 👈 add this
    ]);
}


    public function getAll($session_id) {
        return Notice::where('session_id', $session_id)
                    ->orderBy('id', 'desc')
                    ->simplePaginate(3);
    }
}