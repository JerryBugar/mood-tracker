<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardStatisticsService;
use App\Services\Admin\DashboardChartService;
use App\Services\Admin\DashboardTabService;
use App\Services\Admin\UserDetailService;
use App\Models\MoodRecord;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;

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

    public function getChartData(Request $request)
    {
        $filterType = $request->get('filter_type');
        $filterValue = $request->get('filter_value');
        $chartType = $request->get('chart_type');

        $chartData = $this->chartService->getAllChartData($filterType, $filterValue, $chartType);
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

        // Ambil data employees dan divisions
        $employees = User::select('id', 'name', 'email')->orderBy('name')->get();
        $divisions = User::select('division')
            ->whereNotNull('division')
            ->distinct()
            ->orderBy('division')
            ->pluck('division')
            ->filter()
            ->values();

        $data = [
            'employees' => $employees,
            'divisions' => $divisions
        ];

        if ($this->tabService->isTurboStreamRequest()) {
            $content = view('admin.tabs.notifications', $data)->render();
            return $this->tabService->createTurboStreamResponse('dashboard_content', $content);
        }

        return view('admin.tabs.notifications', $data);
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

    /**
     * Menyimpan respons admin untuk mood record
     *
     * @param int $recordId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveAdminResponse($recordId, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admin_response' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $moodRecord = MoodRecord::find($recordId);

        if (!$moodRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Mood record tidak ditemukan'
            ], 404);
        }

        $moodRecord->admin_response = $request->admin_response;
        $moodRecord->admin_response_at = now();
        $moodRecord->save();

        return response()->json([
            'success' => true,
            'message' => 'Respons berhasil disimpan',
            'admin_response' => $moodRecord->admin_response,
            'admin_response_at' => $moodRecord->admin_response_at
        ]);
    }

    /**
     * Mengirim notifikasi
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:individual,group,all',
            'message' => 'required|string|max:1000',
            'user_id' => 'required_if:type,individual|nullable|exists:users,id',
            'division' => 'required_if:type,group|nullable|string',
            'scheduled_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Parse scheduled_at jika ada
            // datetime-local mengirim format: YYYY-MM-DDTHH:mm (tanpa timezone)
            // Asumsikan waktu input adalah waktu lokal (WIB/UTC+7)
            $scheduledAt = null;
            if ($request->scheduled_at) {
                // Parse waktu dari input (format: YYYY-MM-DDTHH:mm)
                // Gunakan timezone aplikasi
                $scheduledAt = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $request->scheduled_at, config('app.timezone'));
            }
            $isScheduled = $scheduledAt && $scheduledAt->isFuture();

            // Buat notifikasi
            $notification = Notification::create([
                'type' => $request->type,
                'message' => $request->message,
                'division' => $request->division,
                'scheduled_at' => $scheduledAt,
                'target_user_id' => $request->type === 'individual' ? $request->user_id : null,
            ]);

            // Tentukan user yang akan menerima notifikasi
            $users = collect();

            if ($request->type === 'individual') {
                $users = User::where('id', $request->user_id)->get();
            } elseif ($request->type === 'group') {
                $users = User::where('division', $request->division)->get();
            } elseif ($request->type === 'all') {
                $users = User::all();
            }

            // Attach users ke notification hanya jika tidak dijadwalkan (langsung kirim)
            // Jika dijadwalkan, tidak perlu attach sekarang - akan diproses oleh middleware
            if ($users && $users instanceof \Illuminate\Support\Collection && $users->isNotEmpty() && !$isScheduled) {
                $notification->users()->attach($users->pluck('id')->toArray());
                
                // Kirim push notification untuk notifikasi langsung
                $notificationService = app(NotificationService::class);
                $notificationService->sendPushNotifications($notification, $users);
            }
            // Untuk scheduled notifications, push notification akan dikirim saat diproses oleh middleware

            DB::commit();

            $message = $isScheduled 
                ? 'Notifikasi berhasil dijadwalkan untuk ' . $users->count() . ' karyawan pada ' . $scheduledAt->format('d/m/Y H:i')
                : 'Notifikasi berhasil dikirim ke ' . $users->count() . ' karyawan';

            return response()->json([
                'success' => true,
                'message' => $message,
                'notification_id' => $notification->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim notifikasi: ' . $e->getMessage()
            ], 500);
        }
    }
}