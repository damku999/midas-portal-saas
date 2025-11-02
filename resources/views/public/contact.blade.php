@extends('public.layout')

@section('title', 'Contact Us - Midas Portal')

@section('content')
<section class="py-5">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <h1 class="display-4 fw-bold">Get In Touch</h1>
                    <p class="lead text-muted">Have questions? We'd love to hear from you.</p>
                </div>

                @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif

                <div class="card border-0 shadow">
                    <div class="card-body p-5">
                        <form action="{{ route('public.contact.submit') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name *</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email *</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" name="phone" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Company</label>
                                    <input type="text" name="company" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message *</label>
                                    <textarea name="message" class="form-control" rows="5" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-gradient px-5">Send Message</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row g-4 mt-5">
                    <div class="col-md-4 text-center">
                        <i class="fas fa-envelope fa-2x text-primary mb-3"></i>
                        <h5>Email</h5>
                        <p class="text-muted">info@midasportal.com</p>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="fas fa-phone fa-2x text-primary mb-3"></i>
                        <h5>Phone</h5>
                        <p class="text-muted">+91 1234567890</p>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="fas fa-map-marker-alt fa-2x text-primary mb-3"></i>
                        <h5>Address</h5>
                        <p class="text-muted">India</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
