<?php

namespace App\Repositories;

use App\Models\Notice;

class NoticeRepository {
 
public function store($data)
{
    return Notice::create([
    'notice' => $data['notice'],
    'session_id' => $data['session_id'],
]);

}



    public function getAll($session_id) {
        return Notice::where('session_id', $session_id)
                    ->orderBy('id', 'desc')
                    ->simplePaginate(3);
    }
}