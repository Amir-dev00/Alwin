<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(): View
    {
        return view('admin.activity.index', [
            'items' => ActivityLog::query()->with('user')->latest()->paginate(30),
        ]);
    }
}
