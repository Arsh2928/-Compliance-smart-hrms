<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function markAsRead(Alert $alert)
    {
        if ($alert->user_id == auth()->id()) {
            $alert->update(['is_read' => true]);
            
            if ($alert->link) {
                return redirect($alert->link);
            }
        }
        
        return back();
    }
}
