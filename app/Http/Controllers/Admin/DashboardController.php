<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardStatisticsService;
use App\Services\Admin\DashboardChartService;
use App\Services\Admin\DashboardTabService;
use App\Services\Admin\UserDetailService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $statisticsService;
    protected $chartService;
    protected $tabService;
    protected $userDetailService;

    public function __construct(
        DashboardStatisticsService $statisticsService,
        DashboardChartService $chartService,
        DashboardTabService $tabService,
        UserDetailService $userDetailService
    ) {
        $this->statisticsService = $statisticsService;
        $this->chartService = $chartService;
        $this->tabService = $tabService;
        $this->userDetailService = $userDetailService;
    }

    public function index()
    {
        $statistics = $this->statisticsService->getDashboardStatistics();

        return view('admin.dashboard', $statistics);
    }

    public function moodMonitoring(Request $request)
    {
        $moodRecords = $this->statisticsService->getMoodMonitoringData($request);

        // Check if this is a Turbo Stream request for records only
        $acceptHeader = request()->header('Accept');
        if (strpos($acceptHeader, 'text/vnd.turbo-stream.html') !== false) {
            $target = $request->header('Turbo-Frame');
            
            if ($target === 'mood_records_frame') {
                $recordsContent = view('admin.mood-monitoring-records', compact('moodRecords'))->render();
                return $this->tabService->createTurboStreamResponse('mood_records_frame', $recordsContent);
            } elseif ($target === 'filter_form_frame') {
                $formContent = view('admin.mood-monitoring-filters', [
                    'division' => $request->get('division'),
                    'mood' => $request->get('mood'),
                    'startDate' => $request->get('start_date'),
                    'endDate' => $request->get('end_date')
                ])->render();
                return $this->tabService->createTurboStreamResponse('filter_form_frame', $formContent);
            } elseif ($target === 'mood_chart_frame') {
                $chartContent = view('admin.mood-monitoring-chart')->render();
                return $this->tabService->createTurboStreamResponse('mood_chart_frame', $chartContent);
            }
        }

        return view('admin.mood-monitoring', compact('moodRecords'));
    }

    public function getChartData()
    {
        $chartData = $this->chartService->getAllChartData();
        return response()->json($chartData);
    }

    public function getUserDetail($id, Request $request)
    {
        $filterType = $request->get('filter_type'); // 'day', 'month', 'year', atau null
        $filterValue = $request->get('filter_value'); // Nilai filter sesuai type

        $userDetail = $this->userDetailService->getUserDetail($id, $filterType, $filterValue);

        if (!$userDetail) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ]);
        }

        return response()->json($userDetail);
    }

    public function employeesTab()
    {
        if (!$this->tabService->isTurboFrameRequest()) {
            return redirect()->route('admin.dashboard');
        }

        $employees = $this->tabService->getEmployeesTabData();

        if ($this->tabService->isTurboStreamRequest()) {
            $content = view('admin.tabs.employees', compact('employees'))->render();
            return $this->tabService->createTurboStreamResponse('dashboard_content', $content);
        }

        return view('admin.tabs.employees', compact('employees'));
    }

    public function notificationsTab()
    {
        if (!$this->tabService->isTurboFrameRequest()) {
            return redirect()->route('admin.dashboard');
        }

        if ($this->tabService->isTurboStreamRequest()) {
            $content = view('admin.tabs.notifications')->render();
            return $this->tabService->createTurboStreamResponse('dashboard_content', $content);
        }

        return view('admin.tabs.notifications');
    }

    public function overviewTab()
    {
        if (!$this->tabService->isTurboFrameRequest()) {
            return redirect()->route('admin.dashboard');
        }

        $viewData = $this->tabService->getOverviewTabData();

        if ($this->tabService->isTurboStreamRequest()) {
            $content = view('admin.tabs.overview', $viewData)->render();
            return $this->tabService->createTurboStreamResponse('dashboard_content', $content);
        }

        return view('admin.tabs.overview', $viewData);
    }
}