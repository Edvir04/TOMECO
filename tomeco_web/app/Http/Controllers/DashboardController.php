<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Admin;
use App\Models\SuperAdmin;
use App\Models\TomecoEnforcer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Calculate statistics
        $totalTickets = Ticket::count();
        $totalUsers = Admin::count() + SuperAdmin::count() + TomecoEnforcer::count();
        $pendingTickets = Ticket::where('status', 'Unpaid')->count();
        
        // Calculate tickets by period
        $todayTickets = Ticket::whereDate('created_at', Carbon::today())->count();
        $weekTickets = Ticket::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $monthTickets = Ticket::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        return view('layout.dashboard', [
            'totalTickets' => $totalTickets,
            'totalUsers' => $totalUsers,
            'pendingTickets' => $pendingTickets,
            'todayTickets' => $todayTickets,
            'weekTickets' => $weekTickets,
            'monthTickets' => $monthTickets,
        ]);
    }

    /**
     * Get all tickets for the modal
     */
    public function getTickets()
    {
        $tickets = Ticket::orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Get all users for the modal
     */
    public function getUsers()
    {
        $admins = Admin::select('id', 'fullname', 'username', 'id_number', 'gender', 'contact_number', 'created_at')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'fullname' => $user->fullname,
                    'username' => $user->username,
                    'id_number' => $user->id_number,
                    'gender' => $user->gender,
                    'contact_number' => $user->contact_number,
                    'role' => 'Admin',
                    'created_at' => $user->created_at,
                ];
            });

        $superAdmins = SuperAdmin::select('id', 'fullname', 'username', 'id_number', 'gender', 'contact_number', 'created_at')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'fullname' => $user->fullname,
                    'username' => $user->username,
                    'id_number' => $user->id_number,
                    'gender' => $user->gender,
                    'contact_number' => $user->contact_number,
                    'role' => 'SuperAdmin',
                    'created_at' => $user->created_at,
                ];
            });

        $enforcers = TomecoEnforcer::select('id', 'fullname', 'username', 'id_number', 'gender', 'contact_number', 'created_at')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'fullname' => $user->fullname,
                    'username' => $user->username,
                    'id_number' => $user->id_number,
                    'gender' => $user->gender,
                    'contact_number' => $user->contact_number,
                    'role' => 'Enforcer',
                    'created_at' => $user->created_at,
                ];
            });

        // Combine all users and sort by created_at
        $allUsers = $admins->merge($superAdmins)->merge($enforcers)
            ->sortByDesc('created_at')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $allUsers
        ]);
    }

    /**
     * Get pending tickets (unpaid status) for the modal
     */
    public function getPendingTickets()
    {
        $tickets = Ticket::where('status', 'Unpaid')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Get tickets issued today
     */
    public function getTodayTickets()
    {
        $tickets = Ticket::whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $tickets,
            'period' => 'Today',
            'count' => $tickets->count()
        ]);
    }

    /**
     * Get tickets issued this week
     */
    public function getWeekTickets()
    {
        $tickets = Ticket::whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $tickets,
            'period' => 'This Week',
            'count' => $tickets->count()
        ]);
    }

    /**
     * Get tickets issued this month
     */
    public function getMonthTickets()
    {
        $tickets = Ticket::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $tickets,
            'period' => 'This Month',
            'count' => $tickets->count()
        ]);
    }

    /**
     * Get all period reports (day, week, month) in one call
     */
    public function getPeriodReports()
    {
        $todayTickets = Ticket::whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->get();
        
        $weekTickets = Ticket::whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $monthTickets = Ticket::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'today' => [
                    'tickets' => $todayTickets,
                    'count' => $todayTickets->count()
                ],
                'week' => [
                    'tickets' => $weekTickets,
                    'count' => $weekTickets->count()
                ],
                'month' => [
                    'tickets' => $monthTickets,
                    'count' => $monthTickets->count()
                ]
            ]
        ]);
    }

    /**
     * Get most repeated violations statistics with date filtering
     */
    public function getViolationsStatistics()
    {
        $query = Ticket::whereNotNull('violations');
        
        // Handle date filtering
        $filter = request()->get('filter', 'all');
        
        switch ($filter) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'month':
                $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'custom':
                $startDate = request()->get('start_date');
                $endDate = request()->get('end_date');
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ]);
                }
                break;
            // 'all' or default - no date filtering
        }
        
        $tickets = $query->get();
        
        $violationCounts = [];
        
        foreach ($tickets as $ticket) {
            if ($ticket->violations && is_array($ticket->violations)) {
                foreach ($ticket->violations as $violation) {
                    if (!empty($violation)) {
                        if (!isset($violationCounts[$violation])) {
                            $violationCounts[$violation] = 0;
                        }
                        $violationCounts[$violation]++;
                    }
                }
            }
        }
        
        // Sort by count (descending) and get top violations
        arsort($violationCounts);
        
        // Convert to array format for JSON response
        $allViolations = [];
        foreach ($violationCounts as $violation => $count) {
            $allViolations[] = [
                'violation' => $violation,
                'count' => $count
            ];
        }
        
        $total = array_sum($violationCounts);
        
        // Calculate percentages
        foreach ($allViolations as &$violation) {
            $violation['percentage'] = $total > 0 ? round(($violation['count'] / $total) * 100, 1) : 0;
        }
        
        return response()->json([
            'success' => true,
            'data' => $allViolations,
            'total' => $total,
            'filter' => $filter
        ]);
    }

    /**
     * Get enforcer ticket issuance statistics with date filtering
     */
    public function getEnforcerStatistics()
    {
        $query = Ticket::whereNotNull('apprehending_officer');

        // Handle date filtering
        $filter = request()->get('filter', 'all');

        switch ($filter) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'month':
                $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'custom':
                $startDate = request()->get('start_date');
                $endDate = request()->get('end_date');
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ]);
                }
                break;
            // 'all' or default - no date filtering
        }

        $stats = $query
            ->select('apprehending_officer', DB::raw('COUNT(*) as count'))
            ->groupBy('apprehending_officer')
            ->orderByDesc('count')
            ->get()
            ->map(function ($row) {
                $officerName = $row->apprehending_officer ?: 'Unknown';
                
                // Try to find enforcer by fullname to get profile picture
                $enforcer = TomecoEnforcer::where('fullname', $officerName)->first();
                $profilePicture = null;
                
                if ($enforcer && $enforcer->profile_picture) {
                    // Handle profile picture URL
                    if (filter_var($enforcer->profile_picture, FILTER_VALIDATE_URL)) {
                        $profilePicture = $enforcer->profile_picture;
                    } else {
                        $storagePath = str_starts_with($enforcer->profile_picture, '/') 
                            ? $enforcer->profile_picture 
                            : Storage::url($enforcer->profile_picture);
                        $profilePicture = asset($storagePath);
                    }
                }
                
                return [
                    'officer' => $officerName,
                    'count' => (int) $row->count,
                    'profile_picture' => $profilePicture,
                ];
            })
            ->values();

        $total = $stats->sum('count');

        $stats = $stats->map(function ($row) use ($total) {
            $row['percentage'] = $total > 0 ? round(($row['count'] / $total) * 100, 1) : 0;
            return $row;
        });

        return response()->json([
            'success' => true,
            'data' => $stats,
            'total' => $total,
            'filter' => $filter
        ]);
    }

    /**
     * Get violator statistics (most repeated violators) with date filtering
     */
    public function getViolatorStatistics()
    {
        $query = Ticket::query();

        // Handle date filtering
        $filter = request()->get('filter', 'all');

        switch ($filter) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'month':
                $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'custom':
                $startDate = request()->get('start_date');
                $endDate = request()->get('end_date');
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ]);
                }
                break;
            // 'all' or default - no date filtering
        }

        // Group by driver's full name (combining firstname, middlename, lastname)
        // Use raw SQL to concatenate and group by the full name
        $stats = $query
            ->select(
                'driver_firstname',
                'driver_middlename',
                'driver_lastname',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('driver_firstname', 'driver_middlename', 'driver_lastname')
            ->get()
            ->map(function ($row) {
                // Construct full name
                $nameParts = array_filter([
                    $row->driver_firstname,
                    $row->driver_middlename,
                    $row->driver_lastname
                ]);
                $fullName = implode(' ', $nameParts) ?: 'Unknown';
                
                return [
                    'violator' => trim($fullName),
                    'count' => (int) $row->count,
                ];
            })
            ->filter(function ($item) {
                // Filter out 'Unknown' or empty names
                return !empty($item['violator']) && $item['violator'] !== 'Unknown';
            })
            ->sortByDesc('count')
            ->values();

        $total = $stats->sum('count');

        // Calculate percentages
        $stats = $stats->map(function ($row) use ($total) {
            $row['percentage'] = $total > 0 ? round(($row['count'] / $total) * 100, 1) : 0;
            return $row;
        });

        return response()->json([
            'success' => true,
            'data' => $stats,
            'total' => $total,
            'filter' => $filter
        ]);
    }
}

