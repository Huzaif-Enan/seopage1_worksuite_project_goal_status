<?php


namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\AttendanceSetting;
use App\Models\DashboardWidget;
use App\Models\EmployeeDetails;
use App\Models\Event;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\ProjectTimeLog;
use App\Models\ProjectTimeLogBreak;
use App\Models\Task;
use App\Models\TaskboardColumn;
use App\Models\Ticket;
use App\Models\PMAssign;
use App\Models\Project;
use App\Traits\ClientDashboard;
use App\Traits\ClientPanelDashboard;
use App\Traits\CurrencyExchange;
use App\Traits\EmployeeDashboard;
use App\Traits\FinanceDashboard;
use App\Traits\HRDashboard;
use App\Traits\OverviewDashboard;
use App\Traits\ProjectDashboard;
use App\Traits\TicketDashboard;
use App\Traits\webdevelopmentDashboard;
use App\Traits\LeadDashboard;
use App\Traits\DeveloperDashboard;
use App\Traits\UxUiDashboard;
use App\Traits\GraphicsDashboard;
use App\Traits\SalesDashboard;
use App\Traits\PmDashboard;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Froiden\Envato\Traits\AppBoot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ClientDelay;
use PHPUnit\Framework\ActualValueIsNotAnObjectException;


class PmPaymentReleaseHistory extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Pm Payment Release History';
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $startDate= '2023-07-01 00:00:00';
        $assignEndDate='2023-07-31 23:59:59';
        $pmId=209;
        

        //Total Assigned Amount(For this Cycle)

        $this->total_assigned_amount_for_this_cycle = Project::join('project_milestones', 'projects.id', '=', 'project_milestones.project_id')
            ->where('projects.pm_id', $pmId)
            ->whereBetween('project_milestones.created_at', [$startDate, $assignEndDate])
            ->sum('cost');

        
        //View transaction history by project manager  payment release date in the particular month//

        $this->transaction_amount_dataview = Project::select('payments.paid_on', 'projects.pm_id','p_m_projects.created_at as project_start_date','users.name as manager_name', 'projects.client_id', 'clients.name', 'projects.id', 'projects.project_name', 'projects.project_budget', 'project_milestones.id as milestone_id', 'project_milestones.milestone_title', 'project_milestones.cost', 'project_milestones.created_at')
            ->join('users',  'users.id', '=', 'projects.pm_id')
            ->join('users as clients', 'clients.id', '=', 'projects.client_id')
            ->join('project_milestones', 'projects.id', '=', 'project_milestones.project_id')
            ->join('p_m_projects', 'projects.id', '=', 'p_m_projects.project_id')
            ->leftJoin('payments', 'project_milestones.invoice_id', '=', 'payments.invoice_id')
            ->whereBetween('payments.paid_on', [$startDate, $assignEndDate])
            ->orderBy('payments.paid_on', 'DESC')
            ->where('projects.pm_id', $pmId)
            ->get();

       
        foreach ($this->transaction_amount_dataview as $project) {
            $project_id = $project->id;
            $payment_date = $project->paid_on;
            $project_budget = $project->project_budget;

            $released_amount_project = DB::table('projects')
                ->join('project_milestones', 'projects.id', '=',
                    'project_milestones.project_id'
                )
                ->join('payments', 'project_milestones.invoice_id', '=', 'payments.invoice_id')
                ->where('projects.id', $project_id)
                ->where('payments.paid_on', '<=', $payment_date)
                ->sum('project_milestones.cost');


            $project->unreleased_amount_project = $project_budget-$released_amount_project;
        }

        
        //Total unrelease amount (Overall)

        $this->pm_pending_milestone_value = DB::table('projects')
            ->join('project_milestones', 'projects.id', '=', 'project_milestones.project_id')
            ->leftJoin('payments', 'project_milestones.invoice_id', '=', 'payments.invoice_id')
            ->where('project_milestones.created_at', '<=', $assignEndDate)
            ->where(function ($q1) use ($assignEndDate) {
                $q1->whereNull('payments.paid_on')
                    ->orWhere('payments.paid_on', '>', $assignEndDate);
            })
            ->whereNot('project_milestones.status', 'canceled')
            ->where('projects.pm_id', $pmId)
            ->sum('project_milestones.cost');

       
        //Pending Amount(upto last month)

        $this->pm_pending_milestone_value_upto_last_month = DB::table('projects')
        ->join('project_milestones', 'projects.id', '=', 'project_milestones.project_id')
        ->leftJoin('payments', 'project_milestones.invoice_id', '=', 'payments.invoice_id')
        ->where('project_milestones.created_at', '<', $startDate)
        ->where(function ($q1) use ($startDate) {
            $q1->whereNull('payments.paid_on')
                ->orWhere('payments.paid_on', '>', $startDate);
        })
        ->whereNot('project_milestones.status', 'canceled')
            ->where('projects.pm_id', $pmId)
            ->sum('project_milestones.cost');


        //Total Unreleased Amount(For this Cycle)

        $this->pm_unreleased_amount_month = DB::table('users')    // this is used for finding upto last month pending and this is selected cycle pending amount and that will be minus to whole pending amount
        ->join('projects', 'users.id', '=', 'projects.pm_id')   //cancel  milestone  is not allowed
        ->join('project_milestones', 'projects.id', '=', 'project_milestones.project_id')
        ->leftJoin('payments', 'project_milestones.invoice_id', '=', 'payments.invoice_id')
        ->whereBetween('project_milestones.created_at', [$startDate, $assignEndDate])
        ->where(function ($q1) use ($assignEndDate) {
            $q1->whereNull('payments.paid_on')
                ->orWhere('payments.paid_on', '>', $assignEndDate);
        })
        ->whereNot('project_milestones.status', 'canceled')
        ->where('users.id', $pmId)
        ->sum('project_milestones.cost');

      
        //Total Released Amount(this Cycle)

        $this->pm_released_amount_month = DB::table('users')
        ->join('projects', 'users.id', '=', 'projects.pm_id')
        ->join('project_milestones', 'projects.id', '=', 'project_milestones.project_id')
        ->join('payments', 'project_milestones.invoice_id', '=', 'payments.invoice_id')
        //->whereNotNull('project_milestones.invoice_id')
        ->whereBetween('payments.paid_on', [$startDate, $assignEndDate])
        ->where('users.id', $pmId)
        ->sum('project_milestones.cost');


        //Total release amount (For this cycles projects)

        $this->pm_released_amount_this_month_create = DB::table('users')
        ->join('projects', 'users.id', '=', 'projects.pm_id')
        ->join('project_milestones', 'projects.id', '=', 'project_milestones.project_id')
        ->join('payments', 'project_milestones.invoice_id', '=', 'payments.invoice_id')
        //->whereNotNull('project_milestones.invoice_id')
        ->whereBetween('project_milestones.created_at', [$startDate, $assignEndDate])
        ->whereBetween('payments.paid_on', [$startDate, $assignEndDate])
        ->where('users.id', $pmId)
        ->sum('project_milestones.cost');
       
        return view('pm-payment.pm_payment_history', $this->data);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
