@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Profile Information</div>

                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Name:</label>
                        <p class="form-control-plaintext">{{ Auth::user()->name }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email:</label>
                        <p class="form-control-plaintext">{{ Auth::user()->email }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Account Type:</label>
                        <p class="form-control-plaintext">
                            {{ ucfirst(str_replace('_', ' ', Auth::user()->user_type)) }}
                        </p>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection