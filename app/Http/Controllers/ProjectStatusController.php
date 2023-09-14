<?php

namespace App\Http\Controllers;

use App\Models\DealStage;
use App\Models\DealStageChange;
use App\Models\kpiSettingGenerateSale;
use App\Models\ProjectCms;
use App\Models\ProjectPortfolio;
use App\Models\ProjectWebsitePlugin;
use App\Models\ProjectWebsiteTheme;
use App\Models\ProjectWebsiteType;
use App\Models\QualifiedSale;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\Team;
use App\Models\RoleUser;
use App\Models\User;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Pinned;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\SubTask;
use App\Models\Currency;
use App\Models\TaskUser;
use App\Models\ProjectFile;
use Illuminate\Http\Request;
use App\Models\ProjectMember;
use App\Imports\ProjectImport;
use App\Jobs\ImportProjectJob;
use App\Models\MessageSetting;
use App\Models\ProjectActivity;
use App\Models\ProjectCategory;
use App\Models\ProjectTemplate;
use App\Models\TaskboardColumn;
use App\Traits\ProjectProgress;
use App\DataTables\TasksDataTable;
use App\Models\DiscussionCategory;
use Illuminate\Support\Facades\DB;
use App\Models\ProjectTimeLogBreak;
use Illuminate\Support\Facades\Bus;
use Maatwebsite\Excel\Facades\Excel;
use App\DataTables\ExpensesDataTable;
use App\DataTables\InvoicesDataTable;
use App\DataTables\PaymentsDataTable;
use App\DataTables\ProjectsDataTable;
use App\DataTables\TimeLogsDataTable;
use App\DataTables\DiscussionDataTable;
use Illuminate\Support\Facades\Artisan;
use Maatwebsite\Excel\HeadingRowImport;
use App\DataTables\ProjectNotesDataTable;
use App\Http\Requests\Project\StoreProject;
use App\DataTables\ArchiveProjectsDataTable;
use App\DataTables\ArchiveTasksDataTable;
use App\Http\Requests\Project\UpdateProject;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use App\Http\Requests\Admin\Employee\ImportRequest;
use App\Http\Requests\Admin\Employee\ImportProcessRequest;
use App\Models\ProjectNote;
use App\Models\ProjectStatusSetting;
use App\Models\PMProject;
use App\Models\PMAssign;
use App\Models\Deal;
use App\Models\DelivarableColumnEdit;
use App\Models\GoalSetting;
use Auth;
use App\Models\Lead;
use Response;
use Illuminate\Support\Facades\App;
use App\Events\ProjectSignedEvent;
use App\Http\Requests\ClientContracts\SignRequest;
use App\Models\ContractSign;
use Illuminate\Support\Facades\File;
use App\Models\ProjectDeliverable;
use Toastr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Models\ProjectDispute;
use Notification;
use App\Notifications\ProjectDisputeNotification;
use App\Models\ProjectMilestone;
use App\Models\ProjectSubmission;
use App\Models\ProjectNiche;
use App\Notifications\ProjectReviewNotification;
use App\Notifications\ProjectReviewAcceptNotification;
use App\Notifications\ProjectSubmissionNotification;
use App\Notifications\ProjectSubmissionAcceptNotification;
use App\Models\QCSubmission;
use App\Models\ProjectDeliverablesClientDisagree;
use App\Notifications\QCSubmissionNotification;
use App\Notifications\QcSubmissionAcceptNotification;
use App\Notifications\ProjectDeliverableTimeExtendNotification;
use App\Notifications\ProjectDeliverableTimeAcceptNotification;
use App\Notifications\DeliverableOthersAuthorizationNotification;
use App\Notifications\DeliverableOthersAuthorizationAcceptNotification;
use App\Notifications\ProjectDeliverableFinalAuthorizationNotification;
use App\Notifications\ProjectDeliverableFinalAuthorizationNotificationAccept;
use App\Notifications\ProjectDelivarableFinalAuthorizationClientNotification;
use App\Models\LeadsDealsActivityLog;
use App\Models\kpiSetting;
use App\Models\CashPoint;
use App\Models\Seopage1Team;
use App\Models\AuthorizationAction;
use App\Models\ProjectGoalStatus;
use App\Models\ProjectStatusNotify;


class ProjectStatusController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'ProjectStatus';

        // $this->middleware(function ($request, $next) {
        //     abort_403(!in_array('tasks', $this->user->modules));
        //     return $next($request);
        // });

    }


    public function index()
    {
        $project_status = DB::table('projects')->select('projects.pm_id','deals.project_type','users.name as manager_name', 'projects.client_id', 'clients.name as client_name', 'projects.id', 'projects.project_name', 'projects.project_budget as budget', 'contract_signs.created_at as project_sign','pm_projects.created_at as project_form')
        ->join('users', 'users.id', '=', 'projects.pm_id')
        ->join('deals', 'deals.id', 'projects.deal_id')
        ->join('users as clients', 'clients.id', '=', 'projects.client_id')
        ->join('p_m_projects as pm_projects', 'pm_projects.project_id', 'projects.id')
        ->leftJoin('contract_signs', 'contract_signs.project_id', '=', 'projects.id')
        ->where('projects.status', 'in progress')
        ->get();

        foreach ($project_status as $project) {
            if($project->project_type =='fixed'){
            $project_id = $project->id;
            $contract_signed_date = $project->project_sign;
            $current_time = Carbon::now();
            $project_form_date= $project->project_form;
            $deliverable_signed_days = Carbon::parse($project->project_form)->addHours(72);
            $events = [];

            $project_deliverable = DB::table('projects')->join('contract_signs', 'contract_signs.project_id', '=', 'projects.id')
            ->where('projects.id', $project_id)
            ->count();

            $tasks_proper_assigned_null = DB::table('project_milestones')
            ->select('project_milestones.id', 'tasks.milestone_id')
            ->leftJoin('tasks', 'tasks.milestone_id', '=', 'project_milestones.id')
            ->whereNull('tasks.milestone_id')
            ->where('project_milestones.project_id', $project_id)
            ->count();

            $tasks_proper_assigned_extend = DB::table('project_milestones')
            ->join('tasks', 'tasks.milestone_id', '=', 'project_milestones.id')
            ->whereRaw('tasks.created_at = (
                 SELECT MIN(created_at) 
                 FROM tasks 
                 WHERE tasks.milestone_id = project_milestones.id)')
            ->where('tasks.created_at', '>', $deliverable_signed_days)
            ->where('project_milestones.project_id', $project_id)
            ->count();

            $tasks_proper_assigned = $tasks_proper_assigned_null + $tasks_proper_assigned_extend;

            if ($deliverable_signed_days <= $current_time && $deliverable_signed_days > $contract_signed_date  && $project_deliverable == 1 && $tasks_proper_assigned == 0) {
                $events[] = (object) ['message' => 'Deliverable is signed and tasks are assigned properly', 'date' => $deliverable_signed_days];
            } else if ($deliverable_signed_days <= $current_time && $deliverable_signed_days > $contract_signed_date && $project_deliverable == 1 && $tasks_proper_assigned >= 1) {
                $events[] = (object) ['message' => 'Deliverable is signed but tasks are not assigned properly', 'date' => $deliverable_signed_days];
            } else if ($deliverable_signed_days <= $current_time && $project_deliverable == 0 && $tasks_proper_assigned == 0) {
                $events[] = (object) ['message' => 'Deliverable is not signed but tasks are assigned properly', 'date' => $deliverable_signed_days];
            } else if ($deliverable_signed_days <= $current_time && $project_deliverable == 0 && $tasks_proper_assigned >= 1) {
                $events[] = (object) ['message' => 'Deliverable is not signed and tasks are not assigned properly', 'date' => $deliverable_signed_days];
            } else if ($deliverable_signed_days <= $current_time && $deliverable_signed_days < $contract_signed_date  && $tasks_proper_assigned == 0) {
                $events[] = (object) ['message' => 'Deliverable is not signed but tasks are assigned properly', 'date' => $deliverable_signed_days];
            } else if ($deliverable_signed_days <= $current_time && $deliverable_signed_days < $contract_signed_date && $tasks_proper_assigned >= 1) {
                $events[] = (object) ['message' => 'Deliverable is not signed and tasks are not assigned properly', 'date' => $deliverable_signed_days];
            }

            $project->events = $events;

            //store data in  project goal status table
            $project_id_string = strval($project->id);
            $deliverable_signed_days_as_string = $deliverable_signed_days->format('YmdHis');
            $short_code= $project_id_string . $deliverable_signed_days_as_string; 

            $project_goal_status = new ProjectGoalStatus();
          
            $project_goal_status->short_code = $short_code;
            $project_goal_status->project_id=$project->id;
            $project_goal_status->pm_id = $project->pm_id;

            foreach ($project->events as $event) {
                $event_message = $event->message; 
                $event_date = $event->date; 
               
            }
            $project_goal_status->event_details= $event_message;
            $project_goal_status->event_date = $event_date;
            $project_goal_status->pm_response = 0;
            $project_goal_status->admin_resolve = 0;

            if($event_message == 'Deliverable is signed and tasks are assigned properly'){
                $project_goal_status->event_status=1;
            } else  $project_goal_status->event_status = 0;

            $project_goal_status->client_id = $project->client_id;

            if ($project->budget >= 0 && $project->budget <= 500) {
                $project_goal_status->project_category = "Regular";
            } elseif ($project->budget >= 501 && $project->budget <= 1000) {
                $project_goal_status->project_category = "Priority";
            } elseif ($project->budget >= 1001 && $project->budget <= 1700) {
                $project_goal_status->project_category = "High-priority";
            } elseif ($project->budget >= 1701 && $project->budget <= 2500) {
                $project_goal_status->project_category = "Top most priority";
            } else {
                $project_goal_status->project_category = "Critically sensitive";
            }

            $find_project_goal_status_code = ProjectGoalStatus::where('short_code', $short_code)->first();

            if($find_project_goal_status_code == NULL){

                $project_goal_status->save();
            }
        //-------------------------------------------after 4 days 1st submission for priority to critical sensitive-------------------------------//

           
           
           if ($project->budget >= 501){

               $priority_submission_client_duration = Carbon::parse($project->project_form)->addHours(96);

                $first_submission_client_priority = DB::table('tasks')
                ->join('task_history', 'tasks.id', '=', 'task_history.task_id')
                ->where('task_history.created_at','<=', $priority_submission_client_duration)
                ->whereNull('tasks.subtask_id')
                ->where('tasks.board_column_id', '=', '9')
                ->where('tasks.project_id', $project_id)
                ->count();

                if($first_submission_client_priority>=1){
                        $event_message= 'The first submission has been completed and is ready for submission to the client';
                  
                }else   $event_message = 'The first submission has not  been completed and is not ready for submission to the client';

           } else{

                    $priority_submission_client_duration = Carbon::parse($project->project_form)->addHours(168);
                    $first_submission_client_priority = DB::table('tasks')
                        ->join('task_history', 'tasks.id', '=', 'task_history.task_id')
                        ->where('task_history.created_at', '<=', $priority_submission_client_duration)
                        ->whereNull('tasks.subtask_id')
                        ->where('tasks.board_column_id', '=', '9')
                        ->where('tasks.project_id', $project_id)
                        ->count();

                    if ($first_submission_client_priority >= 1) {
                        $event_message = 'The first submission has been completed and is ready for submission to the client';
                    } else   $event_message = 'The first submission has not  been completed and is not ready for submission to the client';

                 
           }
              if($priority_submission_client_duration < $current_time){

                $priority_submission_client_duration_string = $priority_submission_client_duration->format('YmdHis');
                $short_code_first_submission = $project_id_string . $priority_submission_client_duration_string; 
        
                $project_goal_status_first_submission = new ProjectGoalStatus();
            
                $project_goal_status_first_submission->short_code = $short_code_first_submission;
                $project_goal_status_first_submission->project_id = $project->id;
                $project_goal_status_first_submission->pm_id = $project->pm_id;
                $project_goal_status_first_submission->event_details = $event_message;
                $project_goal_status_first_submission->event_date = $priority_submission_client_duration;
                $project_goal_status_first_submission->pm_response = 0;
                $project_goal_status_first_submission->admin_resolve = 0;

                if ($event_message == 'The first submission has been completed and is ready for submission to the client') {
                    $project_goal_status_first_submission->event_status = 1;
                } else  $project_goal_status_first_submission->event_status = 0;

                $project_goal_status_first_submission->client_id = $project->client_id;

                if ($project->budget >= 0 && $project->budget <= 500) {
                    $project_goal_status_first_submission->project_category = "Regular";
                }
                elseif ($project->budget >= 501 && $project->budget <= 1000) {
                    $project_goal_status_first_submission->project_category = "Priority";
                } elseif ($project->budget >= 1001 && $project->budget <= 1700) {
                    $project_goal_status_first_submission->project_category = "High-priority";
                } elseif ($project->budget >= 1701 && $project->budget <= 2500) {
                    $project_goal_status_first_submission->project_category = "Top most priority";
                } else {
                    $project_goal_status_first_submission->project_category = "Critically sensitive";
                }

                $find_project_goal_status_code_first_submission = ProjectGoalStatus::where('short_code', $short_code_first_submission)->first();

                if ($find_project_goal_status_code_first_submission == NULL) {

                    $project_goal_status_first_submission->save();
                }
            }


            //----------------------------------------Milestones Calculation----------------------------------------------------------------------------------//

                $milestone_assign_project = DB::table('project_milestones')
                ->where('project_id', $project_id)
                ->where('status','!=', 'Canceled')
                ->count();

                if($project->budget <= 500) {

                    $reguler_half_milestone_duration = Carbon::parse($project->project_form)->addHours(288);
                    
                    if($reguler_half_milestone_duration < $current_time){

                        $milestone_release_regular_tweleve_days = DB::table('project_milestones')
                         ->join('payments', 'project_milestones.invoice_id', '=', 'payments.invoice_id')
                         ->where('project_milestones.project_id', $project_id)
                         ->whereBetween('payments.paid_on', [$project_form_date, $reguler_half_milestone_duration])
                         ->count();
                        $milestone_assign_project_string = strval($milestone_assign_project);

                         if($milestone_release_regular_tweleve_days >= $milestone_assign_project/2){

                            $event_message ='The number of milestones are '. $milestone_assign_project_string.' and 50% of the milestones of this project has been released';
                            $event_status=1;
                         }else{
                            $event_message = 'The number of milestones are '.$milestone_assign_project_string .' and 50% of the milestones of this project could not be released';
                            $event_status = 0;
                         }

                        $reguler_half_milestone_duration_string = $reguler_half_milestone_duration->format('YmdHis');
                        $short_code_reguler_half_milestone = $project_id_string . $reguler_half_milestone_duration_string;

                        $project_goal_status_half_milestone = new ProjectGoalStatus();

                        $project_goal_status_half_milestone->short_code = $short_code_reguler_half_milestone;
                        $project_goal_status_half_milestone->project_id = $project->id;
                        $project_goal_status_half_milestone->pm_id = $project->pm_id;
                        $project_goal_status_half_milestone->event_details = $event_message;
                        $project_goal_status_half_milestone->event_date = $reguler_half_milestone_duration;
                        $project_goal_status_half_milestone->pm_response = 0;
                        $project_goal_status_half_milestone->admin_resolve = 0;
                        $project_goal_status_half_milestone->event_status = $event_status;
                        $project_goal_status_half_milestone->client_id = $project->client_id;
                        $project_goal_status_half_milestone->project_category = "Regular";

                        $find_project_goal_status_half_milestone = ProjectGoalStatus::where('short_code', $short_code_reguler_half_milestone)->first();

                        if ($find_project_goal_status_half_milestone == NULL) {

                            $project_goal_status_half_milestone->save();
                        }

                    }
                     $milestone_count_this_week=  $milestone_assign_project/2 +1;
                     $milestone_count_this_week = intval($milestone_count_this_week);
                     $one_milestone_release_week_duration = Carbon::parse($project->project_form)->addHours(360);
                    
                    while($one_milestone_release_week_duration < $current_time){

                        $one_milestone_release_week = DB::table('project_milestones')
                            ->join('payments', 'project_milestones.invoice_id', '=', 'payments.invoice_id')
                            ->where('project_milestones.project_id', $project_id)
                            ->whereBetween('payments.paid_on', [$project_form_date, $one_milestone_release_week_duration])
                            ->count();
                        
                        if($milestone_count_this_week > $milestone_assign_project){

                            $milestone_count_this_week= $milestone_assign_project;

                        }
                        $milestone_count_this_week_string = strval($milestone_count_this_week);
                        
                       $total_released_milestone_goal = $milestone_count_this_week - $one_milestone_release_week;
                       $total_released_milestone_goal_string = strval($total_released_milestone_goal);
                       $accomplished_this_week_milestone = strval($milestone_count_this_week);

                       if($total_released_milestone_goal <=0){

                            $event_message = $accomplished_this_week_milestone.' milestone realesed goal for this week is accomplished ';
                            $event_status = 1;
                       }else {
                            $event_message =$total_released_milestone_goal_string.' out of '.$milestone_count_this_week_string.' milestone could not be released in this week';
                            $event_status = 0;
                           
                       }

                        $reguler_week_milestone_duration_string = $one_milestone_release_week_duration->format('YmdHis');
                        $short_code_reguler_week_milestone_duration_string= $project_id_string . $reguler_week_milestone_duration_string;

                        $project_goal_status_week_milestone_release = new ProjectGoalStatus();

                        $project_goal_status_week_milestone_release->short_code = $short_code_reguler_week_milestone_duration_string;
                        $project_goal_status_week_milestone_release->project_id = $project->id;
                        $project_goal_status_week_milestone_release->pm_id = $project->pm_id;
                        $project_goal_status_week_milestone_release->event_details = $event_message;
                        $project_goal_status_week_milestone_release->event_date = $one_milestone_release_week_duration;
                        $project_goal_status_week_milestone_release->pm_response = 0;
                        $project_goal_status_week_milestone_release->admin_resolve = 0;
                        $project_goal_status_week_milestone_release->event_status = $event_status;
                        $project_goal_status_week_milestone_release->client_id = $project->client_id;
                        $project_goal_status_week_milestone_release->project_category = "Regular";

                        $find_project_goal_status_week_milestone_release  = ProjectGoalStatus::where('short_code', $short_code_reguler_week_milestone_duration_string)->first();

                        if ($find_project_goal_status_week_milestone_release  == NULL) {

                            $project_goal_status_week_milestone_release->save();
                        }
                       
                        $milestone_count_this_week++;
                        $one_milestone_release_week_duration = Carbon::parse($one_milestone_release_week_duration)->addHours(168);
                        
                    }

                }else{

                    $milestone_count_this_week = 1;
                    $one_milestone_release_week_duration = Carbon::parse($project->project_form)->addHours(168);
                    $seven_days_duration= $one_milestone_release_week_duration;
                    $twelve_days_duration= Carbon::parse($project->project_form)->addHours(288);

                    while ($one_milestone_release_week_duration < $current_time) {

                        $milestone_count_this_week_string = strval($milestone_count_this_week);
                        $one_milestone_release_week = DB::table('project_milestones')
                            ->join('payments', 'project_milestones.invoice_id', '=', 'payments.invoice_id')
                            ->where('project_milestones.project_id', $project_id)
                            ->whereBetween('payments.paid_on', [$project_form_date, $one_milestone_release_week_duration])
                            ->count();

                        if ($milestone_count_this_week > $milestone_assign_project) {

                            $milestone_count_this_week = $milestone_assign_project;
                        }

                        $total_released_milestone_goal = $milestone_count_this_week - $one_milestone_release_week;
                        $total_released_milestone_goal_string = strval($total_released_milestone_goal);
                        $accomplished_this_week_milestone= strval($milestone_count_this_week);

                        if ($total_released_milestone_goal <= 0) {

                            $event_message = $accomplished_this_week_milestone.' milestone realesed goal for this week is accomplished ';
                            $event_status = 1;
                        } else {
                            $event_message = $total_released_milestone_goal_string.' out of '.$milestone_count_this_week_string. ' milestone could not be released in this week';
                            $event_status = 0;
                        }

                        $priority_week_milestone_duration_string = $one_milestone_release_week_duration->format('YmdHis');
                        $short_code_priority_week_milestone_duration_string = $project_id_string . $priority_week_milestone_duration_string;

                        $project_goal_status_week_milestone_release = new ProjectGoalStatus();

                        $project_goal_status_week_milestone_release->short_code = $short_code_priority_week_milestone_duration_string;
                        $project_goal_status_week_milestone_release->project_id = $project->id;
                        $project_goal_status_week_milestone_release->pm_id = $project->pm_id;
                        $project_goal_status_week_milestone_release->event_details = $event_message;
                        $project_goal_status_week_milestone_release->event_date = $one_milestone_release_week_duration;
                        $project_goal_status_week_milestone_release->pm_response = 0;
                        $project_goal_status_week_milestone_release->admin_resolve = 0;
                        $project_goal_status_week_milestone_release->event_status = $event_status;
                        $project_goal_status_week_milestone_release->client_id = $project->client_id;

                        if ($project->budget >= 501 && $project->budget <= 1000) {
                            $project_goal_status_week_milestone_release->project_category = "Priority";
                        } elseif ($project->budget >= 1001 && $project->budget <= 1700) {
                            $project_goal_status_week_milestone_release->project_category = "High-priority";
                        } elseif ($project->budget >= 1701 && $project->budget <= 2500) {
                            $project_goal_status_week_milestone_release->project_category = "Top most priority";
                        } else {
                            $project_goal_status_week_milestone_release->project_category = "Critically sensitive";
                        }

                        $find_project_goal_status_week_milestone_release  = ProjectGoalStatus::where('short_code', $short_code_priority_week_milestone_duration_string)->first();

                        if ($find_project_goal_status_week_milestone_release  == NULL) {

                            $project_goal_status_week_milestone_release->save();
                        }

                        if($one_milestone_release_week_duration == $seven_days_duration){
                            $one_milestone_release_week_duration= $twelve_days_duration;   

                        }else if($one_milestone_release_week_duration == $twelve_days_duration){
                            $one_milestone_release_week_duration = Carbon::parse($one_milestone_release_week_duration)->addHours(72);

                        }else{
                            $one_milestone_release_week_duration = Carbon::parse($one_milestone_release_week_duration)->addHours(168);
                        }

                        $milestone_count_this_week++;
                        
                    }

                }    
        } 
        

      }

        
        $user_id = Auth::id();
        //$role_id = RoleUser::join('users','users.id','=','role_user.user_id')->where('role_user.user_id', $user_id)->select('role_user.role_id')->first();
        $role_id =User::select('users.*')->where('id',$user_id)->get();
        foreach($role_id as $row){
            $this->user_role_id= $row->role_id;
        }


     if($this->user_role_id == 1){

            $this->view_project_status = ProjectGoalStatus::select('project_goal_statuses.*', 'users.name as manager_name', 'projects.project_name', 'projects.project_budget', 'clients.name as client_name', 'pm_projects.created_at as project_start')
            ->join('users',  'users.id', '=', 'project_goal_statuses.pm_id')
            ->join('users as clients',  'clients.id', '=', 'project_goal_statuses.client_id')
            ->join('projects', 'projects.id', '=', 'project_goal_statuses.project_id')
            ->join('p_m_projects as pm_projects', 'pm_projects.project_id', 'projects.id')
            ->orderBy('project_goal_statuses.project_id', 'DESC')
            ->get();


     }else{

            $this->view_project_status = ProjectGoalStatus::select('project_goal_statuses.*', 'users.name as manager_name', 'projects.project_name', 'projects.project_budget', 'clients.name as client_name', 'pm_projects.created_at as project_start')
                ->join('users',  'users.id', '=', 'project_goal_statuses.pm_id')
                ->join('users as clients',  'clients.id', '=', 'project_goal_statuses.client_id')
                ->join('projects', 'projects.id', '=', 'project_goal_statuses.project_id')
                ->join('p_m_projects as pm_projects', 'pm_projects.project_id', 'projects.id')
                ->where('project_goal_statuses.pm_id', $user_id)
                ->orderBy('project_goal_statuses.project_id', 'DESC')
                ->get();

     }
      
                                  
        return view('projects.projectstatus',$this->data);
    }


    public function statusRequestForm($id)
    {

        $this->id = $id;
        //$project_status = ProjectGoalStatus::find($id);
        $this->view_project_status = ProjectGoalStatus::select('project_goal_statuses.*', 'users.name as manager_name', 'projects.project_name', 'projects.project_budget', 'clients.name as client_name', 'pm_projects.created_at as project_start')
        ->join('users',  'users.id', '=', 'project_goal_statuses.pm_id')
        ->join('users as clients',  'clients.id', '=', 'project_goal_statuses.client_id')
        ->join('projects', 'projects.id', '=', 'project_goal_statuses.project_id')
        ->join('p_m_projects as pm_projects', 'pm_projects.project_id', 'projects.id')
        ->where('project_goal_statuses.id', $id)
        ->first();

        return view('projects.statusRequestForm', $this->data);

    }

    public function managerReasonEvent(Request $request)
    {
        
        $project_status = ProjectGoalStatus::find($request->event_id);
        $project_status->pm_reason= $request->manager_reason;
        $project_status->pm_response=1;
        $project_status->save();

        // return Redirect::back();
        return redirect()->route('project-status-index');

    }

    public function statusReviewForm($id)
    {

        $this->id = $id;
        $this->view_project_status = ProjectGoalStatus::select('project_goal_statuses.*', 'users.name as manager_name', 'projects.project_name', 'projects.project_budget', 'clients.name as client_name', 'pm_projects.created_at as project_start')
        ->join('users',  'users.id', '=', 'project_goal_statuses.pm_id')
        ->join('users as clients',  'clients.id', '=', 'project_goal_statuses.client_id')
        ->join('projects', 'projects.id', '=', 'project_goal_statuses.project_id')
        ->join('p_m_projects as pm_projects', 'pm_projects.project_id', 'projects.id')
        ->where('project_goal_statuses.id', $id)
        ->first();

        return view('projects.statusReviewForm', $this->data);
    }

    public function adminReviewEvent(Request $request)
    {

        $project_status = ProjectGoalStatus::find($request->event_id);
        $project_status->admin_rating = $request->admin_rating;
        $project_status->admin_suggest = $request->admin_suggest;
        $project_status->admin_review = $request->admin_review;
        $project_status->admin_resolve =1;
        $project_status->save();

        // return Redirect::back();
        return redirect()->route('project-status-index');
    }

}
