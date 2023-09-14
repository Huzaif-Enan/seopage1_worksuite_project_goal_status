@extends('layouts.app')

@section('filter-section')

@endsection

@section('content')
<div class="container">
    <p><b> Project Name: </b>{{$view_project_status->project_name}}</p>
    <p><b> Client Name:</b> {{$view_project_status->client_name}}</p>
    <p><b> Project Manager Name:</b> {{$view_project_status->manager_name}}</p>
    <p><b> Project Budget:</b> {{$view_project_status->project_budget}} Dollar</p>
    <p><b> Project Category:</b> {{$view_project_status->project_category}}</p>
    <p><b> Project Start Date:</b> {{$view_project_status->project_start}}</p>
    <p><b> Deadline: </b>{{$view_project_status->event_date}}</p>
    <p><b> Description:</b> {{ $view_project_status->event_details }}</p>
    <p><b> Reason:</b> {{ $view_project_status->pm_reason }}</p>


    <div class="form-group">
        <form action="{{ route('admin-review-event') }}"
              method="POST">
            {{ csrf_field() }}
            <input type="hidden"
                   name="event_id"
                   value="{{$view_project_status->id}}">
            <label for="admin_rating"><b>Rating:</b></label><br>
            <select name="admin_rating" id="admin_rating" style="width: 290px;">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
            </select><br>
            <label for="admin_suggest"><b>Suggestion:</b></label><br>
            <textarea name="admin_suggest"
                      id="text"
                      rows="5"
                      cols="40"
                      required></textarea><br>
             <label for="admin_review"><b>Comment:</b></label><br>
            <textarea name="admin_review"
                              id="text"
                              rows="5"
                              cols="40"
                              required></textarea><br>

            <button type="submit"
                    class="btn btn-primary">Submit</button>
        </form>
    </div>

</div>



@endsection