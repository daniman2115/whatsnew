@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p>Welcome back, {{ Auth::user()->name }}!</p>
                    <p>Your account type: <strong>{{ ucfirst(str_replace('_', ' ', Auth::user()->user_type)) }}</strong></p>
                    
                    <div class="mt-4">
                        <h5>Account Actions:</h5>
                        <ul class="list-group">
                            <li class="list-group-item">
                                <a href="{{ route('profile') }}">View Profile</a>
                            </li>
                            <li class="list-group-item">
                                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Logout
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection