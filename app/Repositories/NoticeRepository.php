<?php

namespace App\Repositories;

use App\Models\Notice;

class NoticeRepository {
 
public function store($data)
{
    return Notice::create([
        'title' => $data['title'] ?? null,
        'description' => $data['description'] ?? null, // 👈 raw
        'class_id' => $data['class_id'] ?? null,
        'session_id' => $data['session_id'] ?? null,
    ]);
}



    public function getAll($session_id) {
        return Notice::where('session_id', $session_id)
                    ->orderBy('id', 'desc')
                    ->simplePaginate(3);
    }
}