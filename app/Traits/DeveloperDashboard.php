<?php

namespace App\Traits;
use App\Models\Leave;
use App\Models\Holiday;
use App\Models\DashboardWidget;
use App\Models\AttendanceSetting;
use App\Models\ProjectTimeLog;
use App\Models\ProjectTimeLogBreak;
use App\Models\Role;
use App\Models\Task;
use \Carbon\Carbon;
use App\Helper\Reply;
use Carbon\CarbonPeriod;
use DB;

trait DeveloperDashboard
{
    public function DeveloperDashboard()
    {
        if (request('mode') == 'today' && request()->ajax()) {
            $this->tasks = Task::withoutGlobalScopes()->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->get();
            $this->todayDeadLineTasks = Task::withoutGlobalScopes()->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->where('due_date', request('startDate'))->get();
            $this->todayStartTasks = Task::withoutGlobalScopes()->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->where('start_date', request('startDate'))->get();

            $html = view('dashboard.ajax.developerdashboard.today', $this->data)->render();

            return Reply::dataOnly([
                'status' => 'success', 
                'html' => $html, 
            ]);
        } elseif (request('mode') == 'month' && request()->ajax()) {
            $date = Carbon::createFromFormat('Y-m-d', request('startDate'));

            $startMonth = $date->startOfMonth()->addDays(20)->toDateString(); 
            $endMonth = $date->startOfMonth()->addMonth(1)->addDays(19)->toDateString(); 

            $this->monthlyTasks = Task::withoutGlobalScopes()->select('tasks.*', 'task_approves.*')
            //->selectRaw('SUM(task_approves.rating + task_approves.rating2 + task_approves.rating3) / 3 as totalRating')
            ->join('task_users', 'task_users.task_id', '=', 'tasks.id')
            ->join('task_approves', 'task_approves.task_id', '=', 'tasks.id')
            ->where('task_users.user_id', $this->user->id)
            ->where(DB::raw('DATE(start_date)'), '>', $startMonth)
            ->where(DB::raw('DATE(start_date)'), '<', $endMonth)
            ->groupBy('tasks.id')
            ->get();

            $this->monthlyPositiveRating = 0;
            $this->monthlyNegativeRating = 0;

            foreach ($this->monthlyTasks as $key => $value) {
                $rating = $value->rating + $value->rating2 + $value->rating3;
                $value->totalRating = $rating / 3;
                if ($value->totalRating > 3) {
                    $this->monthlyPositiveRating++;
                } else {
                    $this->monthlyNegativeRating++;
                }
            }

            $this->monthlyToDo = Task::withoutGlobalScopes()->select('tasks.*')->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->whereBetween('start_date', [$startMonth, $endMonth])->where('board_column_id', 2)->get();
            $this->monthlyDoing = Task::withoutGlobalScopes()->select('tasks.*')->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->whereBetween('start_date', [$startMonth, $endMonth])->where('board_column_id', 3)->get();
            $this->monthlyOverdue = Task::withoutGlobalScopes()->select('tasks.*')->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->whereBetween('start_date', [$startMonth, $endMonth])->where('board_column_id', 7)->get();
            $this->monthlyUnderReview = Task::withoutGlobalScopes()->select('tasks.*')->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->whereBetween('start_date', [$startMonth, $endMonth])->where('board_column_id', 6)->get();

            $html = view('dashboard.ajax.developerdashboard.month', $this->data)->render();

            return Reply::dataOnly([
                'status' => 'success', 
                'html' => $html, 
            ]);
        } elseif (request('mode') == 'general' && request()->ajax()) {
            $startDate  = (request('startDate') != '') ? Carbon::createFromFormat($this->global->date_format, request('startDate')) : now($this->global->timezone)->startOfMonth();
            $endDate = (request('endDate') != '') ? Carbon::createFromFormat($this->global->date_format, request('endDate')) : now($this->global->timezone);

            $this->yearlyTasks = Task::withoutGlobalScopes()->select('tasks.*', 'task_approves.*')
            ->join('task_users', 'task_users.task_id', '=', 'tasks.id')
            ->join('task_approves', 'task_approves.task_id', '=', 'tasks.id')
            ->where('task_users.user_id', $this->user->id)
            ->where(DB::raw('DATE(start_date)'), '>', $startDate)
            ->where(DB::raw('DATE(start_date)'), '<', $endDate)
            ->groupBy('tasks.id')
            ->get();
            
            $this->yearlyPositiveRating = 0;
            $this->yearlyNegativeRating = 0;

            foreach ($this->yearlyTasks as $key => $value) {
                $rating = $value->rating + $value->rating2 + $value->rating3;
                $value->totalRating = $rating / 3;
                if ($value->totalRating > 3) {
                    $this->yearlyPositiveRating++;
                } else {
                    $this->yearlyNegativeRating++;
                }
            }

            $this->yearlyToDo = Task::withoutGlobalScopes()->select('tasks.*')->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->whereBetween('start_date', [$startDate, $endDate])->where('board_column_id', 2)->get();
            $this->yearlyDoing = Task::withoutGlobalScopes()->select('tasks.*')->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->whereBetween('start_date', [$startDate, $endDate])->where('board_column_id', 3)->get();
            $this->yearlyOverdue = Task::withoutGlobalScopes()->select('tasks.*')->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->whereBetween('start_date', [$startDate, $endDate])->where('board_column_id', 7)->get();
            $this->yearlyUnderReview = Task::withoutGlobalScopes()->select('tasks.*')->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->whereBetween('start_date', [$startDate, $endDate])->where('board_column_id', 6)->get();

            $html = view('dashboard.ajax.developerdashboard.general', $this->data)->render();

            return Reply::dataOnly([
                'status' => 'success', 
                'html' => $html,
            ]);
        } else {
            $this->startDate  = (request('startDate') != '') ? Carbon::createFromFormat($this->global->date_format, request('startDate')) : now($this->global->timezone)->startOfMonth();
            $this->endDate = (request('endDate') != '') ? Carbon::createFromFormat($this->global->date_format, request('endDate')) : now($this->global->timezone);

            $this->viewEventPermission = user()->permission('view_events');
            $this->viewNoticePermission = user()->permission('view_notice');
            $this->editTimelogPermission = user()->permission('edit_timelogs');
            $this->widgets = DashboardWidget::where('dashboard_type', 'private-dashboard')->get();
            $this->activeWidgets = $this->widgets->filter(function ($value, $key) {
                return $value->status == '1';
            })->pluck('widget_name')->toArray();
            // Getting Attendance setting data
            $this->myActiveTimer = ProjectTimeLog::with('task', 'user', 'project', 'breaks', 'activeBreak')
            ->where('user_id', user()->id)
            ->whereNull('end_time')
            ->first();

            if (!is_null($this->viewNoticePermission) && $this->viewNoticePermission != 'none') {
                if ($this->viewNoticePermission == 'added') {
                    $this->notices = Notice::latest()->where('added_by', $this->user->id)
                    ->select('id', 'heading', 'created_at')
                    ->limit(10)
                    ->get();
                }
                elseif ($this->viewNoticePermission == 'owned') {
                    $this->notices = Notice::latest()
                    ->select('id', 'heading', 'created_at')
                    ->where(['to' => 'employee', 'department_id' => null])
                    ->orWhere(['department_id' => $this->user->employeeDetails->department_id])
                    ->limit(10)
                    ->get();
                }
                elseif ($this->viewNoticePermission == 'both') {
                    $this->notices = Notice::latest()
                    ->select('id', 'heading', 'created_at')
                    ->where('added_by', $this->user->id)
                    ->orWhere(function ($q) {
                        $q->where(['to' => 'employee', 'department_id' => null])
                        ->orWhere(['department_id' => $this->user->employeeDetails->department_id]);
                    })
                    ->limit(10)
                    ->get();
                }
                elseif ($this->viewNoticePermission == 'all') {
                    $this->notices = Notice::latest()
                    ->select('id', 'heading', 'created_at')
                    ->limit(10)
                    ->get();
                }
            }
            
            if (request('start') && request('end') && !is_null($this->viewEventPermission) && $this->viewEventPermission != 'none') {
                $eventData = array();

                $events = Event::with('attendee', 'attendee.user');

                if ($this->viewEventPermission == 'added') {
                    $events->where('events.added_by', $this->user->id);
                }
                elseif ($this->viewEventPermission == 'owned' || $this->viewEventPermission == 'both') {
                    $events->where('events.added_by', $this->user->id)
                    ->orWhere(function ($q) {
                        $q->whereHas('attendee.user', function ($query) {
                            $query->where('user_id', $this->user->id);
                        });
                    });
                }

                $events = $events->get();

                foreach ($events as $key => $event) {
                    $eventData[] = [
                        'id' => $event->id,
                        'title' => ucfirst($event->event_name),
                        'start' => $event->start_date_time,
                        'end' => $event->end_date_time,
                        'extendedProps' => ['bg_color' => $event->label_color, 'color' => '#fff'],
                    ];
                }

                return $eventData;

            }

            /*-----------------------------------*/
            $showClockIn = AttendanceSetting::first();
            $this->showClockIn = AttendanceSetting::first();
            $now = now(global_setting()->timezone);
            $this->weekStartDate = $now->copy()->startOfWeek($showClockIn->week_start_from);
            $this->weekEndDate = $this->weekStartDate->copy()->addDays(7);
            $this->weekPeriod = CarbonPeriod::create($this->weekStartDate, $this->weekStartDate->copy()->addDays(6));
            $this->weekWiseTimelogs = ProjectTimeLog::weekWiseTimelogs($this->weekStartDate->copy()->toDateString(), $this->weekEndDate->copy()->toDateString(), user()->id);
            $this->weekWiseTimelogBreak = ProjectTimeLogBreak::weekWiseTimelogBreak($this->weekStartDate->toDateString(), $this->weekEndDate->toDateString(), user()->id);
            $this->dateWiseTimelogs = ProjectTimeLog::dateWiseTimelogs(now()->toDateString(), user()->id);
            $this->dateWiseTimelogBreak = ProjectTimeLogBreak::dateWiseTimelogBreak(now()->toDateString(), user()->id);
            
            $this->checkTodayLeave = Leave::where('status', 'approved')
            ->select('id')
            ->where('leave_date', now(global_setting()->timezone)->toDateString())
            ->where('user_id', user()->id)
            ->where('duration', '<>', 'half day')
            ->first();
            $currentDate = now(global_setting()->timezone)->format('Y-m-d');
            $this->checkTodayHoliday = Holiday::where('date', $currentDate)->first();

            $this->event_filter = explode(',', user()->employeeDetails->calendar_view);
            
            $this->tasks = Task::withoutGlobalScopes()->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->get();

            $this->todayDeadLineTasks = Task::withoutGlobalScopes()->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->where('due_date', Carbon::today()->format('Y-m-d'))->get();
            $this->todayStartTasks = Task::withoutGlobalScopes()->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->where('start_date', Carbon::today()->format('Y-m-d'))->get();

            $today = Carbon::today()->format('d');
            if ($today > 20) {
                $startMonth = Carbon::now()->startOfMonth()->addDays(20)->toDateString(); 
                $endMonth = Carbon::now()->startOfMonth()->addMonth(1)->addDays(19)->toDateString();  
            } else {
                $startMonth = Carbon::now()->startOfMonth()->subMonths(1)->addDays(20)->toDateString(); 
                $endMonth = Carbon::now()->startOfMonth()->addDays(19)->toDateString(); 
            }
            
            $this->monthlyTasks = Task::withoutGlobalScopes()->select('tasks.*', 'task_approves.*')
            ->join('task_users', 'task_users.task_id', '=', 'tasks.id')
            ->join('task_approves', 'task_approves.task_id', '=', 'tasks.id')
            ->where('task_users.user_id', $this->user->id)->whereBetween('start_date', [$startMonth, $endMonth])
            ->groupBy('tasks.id')
            ->get();

            $this->monthlyPositiveRating = 0;
            $this->monthlyNegativeRating = 0;

            foreach ($this->monthlyTasks as $key => $value) {
                $rating = (int) $value->rating + (int) $value->rating2 + (int) $value->rating3;
                $value->totalRating = $rating / 3;
                if ($value->totalRating > 3) {
                    $this->monthlyPositiveRating++;
                } else {
                    $this->monthlyNegativeRating++;
                }
            }

            $this->monthlyToDo = Task::withoutGlobalScopes()->select('tasks.*')->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->whereBetween('start_date', [$startMonth, $endMonth])->where('board_column_id', 2)->get();
            $this->monthlyDoing = Task::withoutGlobalScopes()->select('tasks.*')->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->whereBetween('start_date', [$startMonth, $endMonth])->where('board_column_id', 3)->get();
            $this->monthlyOverdue = Task::withoutGlobalScopes()->select('tasks.*')->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->whereBetween('start_date', [$startMonth, $endMonth])->where('board_column_id', 7)->get();
            $this->monthlyUnderReview = Task::withoutGlobalScopes()->select('tasks.*')->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->whereBetween('start_date', [$startMonth, $endMonth])->where('board_column_id', 6)->get();

            $this->yearlyTasks = Task::withoutGlobalScopes()->select('tasks.*', 'task_approves.*')
            ->join('task_users', 'task_users.task_id', '=', 'tasks.id')
            ->join('task_approves', 'task_approves.task_id', '=', 'tasks.id')
            ->where('task_users.user_id', $this->user->id)->whereBetween('start_date', [Carbon::now()->subMonths(12), Carbon::now()])
            ->groupBy('tasks.id')
            ->get();

            $this->yearlyPositiveRating = 0;
            $this->yearlyNegativeRating = 0;

            foreach ($this->yearlyTasks as $key => $value) {
                $rating = (int) $value->rating + (int) $value->rating2 + (int) $value->rating3;
                $value->totalRating = $rating / 3;
                if ($value->totalRating > 3) {
                    $this->yearlyPositiveRating++;
                } else {
                    $this->yearlyNegativeRating++;
                }
            }

            $this->yearlyToDo = Task::withoutGlobalScopes()->select('tasks.*')->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->whereBetween('start_date', [Carbon::now()->subMonths(12), Carbon::now()])->where('board_column_id', 2)->get();
            $this->yearlyDoing = Task::withoutGlobalScopes()->select('tasks.*')->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->whereBetween('start_date', [Carbon::now()->subMonths(12), Carbon::now()])->where('board_column_id', 3)->get();
            $this->yearlyOverdue = Task::withoutGlobalScopes()->select('tasks.*')->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->whereBetween('start_date', [Carbon::now()->subMonths(12), Carbon::now()])->where('board_column_id', 7)->get();
            $this->yearlyUnderReview = Task::withoutGlobalScopes()->select('tasks.*')->join('task_users', 'task_users.task_id', '=', 'tasks.id')->where('task_users.user_id', $this->user->id)->whereBetween('start_date', [Carbon::now()->subMonths(12), Carbon::now()])->where('board_column_id', 6)->get();

            if (request()->ajax()) {
                $html = view('dashboard.ajax.developerdashboard', $this->data)->render();
                
                return Reply::dataOnly([
                    'status' => 'success', 
                    'html' => $html, 
                    'title' => $this->pageTitle
                ]);
            } else {
                return view('dashboard.employee.developer', $this->data);
            }
        }
    }
}