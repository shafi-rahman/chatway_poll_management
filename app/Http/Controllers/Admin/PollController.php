<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class PollController extends Controller
{
    
    //listing the polls
    public function index(): View
    {
        return view('admin.polls.index');
    }

    //creating a new poll.
    public function create(): View
    {
        return view('admin.polls.create');
    }
}