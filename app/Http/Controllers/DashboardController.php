<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService) {}

    public function index()
    {
        $user = Auth::user();

        [$role, $data] = match (true) {
            $user->isAdmin()      => ['admin',      $this->dashboardService->adminData()],
            $user->isSupervisor() => ['supervisor', $this->dashboardService->supervisorData($user)],
            $user->isAgent()      => ['agent',      $this->dashboardService->agentData($user)],
            default               => ['customer',   $this->dashboardService->customerData($user)],
        };

        return view('dashboard', array_merge(['dashboardRole' => $role], $data));
    }
}
