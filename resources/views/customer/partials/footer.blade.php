<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3 col-6">
                        <a href="{{ route('customer.policies') }}" class="btn btn-webmonks w-100 btn-sm">
                            <i class="fas fa-shield-alt me-1"></i>Policies
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="{{ route('customer.quotations') }}" class="btn btn-outline-primary w-100 btn-sm">
                            <i class="fas fa-calculator me-1"></i>Quotations
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="{{ route('customer.profile') }}" class="btn btn-outline-secondary w-100 btn-sm">
                            <i class="fas fa-user me-1"></i>Profile
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="{{ route('customer.change-password') }}" class="btn btn-outline-warning w-100 btn-sm">
                            <i class="fas fa-key me-1"></i>Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Help & Support Modal -->
<div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0"
                style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
                <h5 class="modal-title text-white fw-bold" id="helpModalLabel">
                    <i class="fas fa-question-circle me-2"></i>
                    Help & Support
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-info-circle text-primary me-2"></i> How to Use This Portal</h6>
                        <ul class="small">
                            <li><strong>Dashboard:</strong> View your family's insurance policies and member information
                            </li>
                            <li><strong>Family Policies:</strong>
                                @if (isset($isHead) && $isHead)
                                    As family head, you can view all family members' policies
                                @else
                                    You can view all policies in your family group
                                @endif
                            </li>
                            <li><strong>Profile:</strong> View and check your personal information</li>
                            <li><strong>Security:</strong> Change your password regularly for security</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-headset text-success me-2"></i> Contact Support</h6>
                        <p class="small">If you need assistance, please contact our support team:</p>
                        <div class="contact-info small">
                            <p><i class="fas fa-phone text-primary me-2"></i><strong>Phone:</strong> <a
                                    href="tel:{{ str_replace(['+', ' '], '', company_phone()) }}">{{ company_phone() }}</a></p>
                            <p><i class="fas fa-envelope text-primary me-2"></i><strong>Email:</strong>
                                <a href="mailto:webmonks.in">darshan@webmonks.in</a>
                            </p>
                            <p><i class="fas fa-clock text-primary me-2"></i><strong>Hours:</strong> Mon-Fri, 9:00 AM -
                                6:00 PM</p>
                        </div>
                    </div>
                </div>

                <hr>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
