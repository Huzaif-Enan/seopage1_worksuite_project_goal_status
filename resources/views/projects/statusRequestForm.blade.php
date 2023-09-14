@extends('layouts.app')

@section('filter-section')

@endsection

@section('content')
<div class="container">
<p><b> Project Name: </b>{{$view_project_status->project_name}}</p>
<p><b> Client Name:</b> {{$view_project_status->client_name}}</p>
<p><b> Project Budget:</b>  {{$view_project_status->project_budget}} Dollar</p>
<p><b> Project Category:</b> {{$view_project_status->project_category}}</p>
<p><b> Project Start Date:</b> {{$view_project_status->project_start}}</p>
<p><b> Deadline: </b>{{$view_project_status->event_date}}</p>
<p><b> Description:</b> {{ $view_project_status->event_details }}</p>


    <div class="form-group">
        <form action="{{ route('manager-reason-event') }}"
              method="POST">
            {{ csrf_field() }}
            <input type="hidden"
                   name="event_id"
                   value="{{$view_project_status->id}}">
    
            <label for="manager_reason"><b>Reason:</b></label><br>
            <textarea name="manager_reason"
                      id="text" rows="5" cols="40"
                      required></textarea><br>
    
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
    
</div>



@endsection