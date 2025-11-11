@extends('public.layout')

@section('title', 'Terms of Service - Legal Terms & Conditions | Midas Portal')
@section('meta_description', 'Midas Portal terms of service. Read our legal terms and conditions for using our insurance management platform.')
@section('meta_keywords', 'terms of service, terms and conditions, user agreement, legal terms, service agreement')

@section('content')
<!-- Page Header -->
<div style="background: var(--gradient-primary); color: white; padding: 60px 0;">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">Terms of Service</h1>
        <p class="lead">Legal terms and conditions for using Midas Portal</p>
        <p class="small">Last Updated: December 15, 2024</p>
    </div>
</div>

<!-- Terms Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h6 class="text-muted mb-3">TABLE OF CONTENTS</h6>
                        <ul class="list-unstyled small">
                            <li class="mb-2"><a href="#acceptance" class="text-decoration-none">Acceptance of Terms</a></li>
                            <li class="mb-2"><a href="#services" class="text-decoration-none">Services Description</a></li>
                            <li class="mb-2"><a href="#account" class="text-decoration-none">Account Registration</a></li>
                            <li class="mb-2"><a href="#usage" class="text-decoration-none">Acceptable Use</a></li>
                            <li class="mb-2"><a href="#payment" class="text-decoration-none">Payment Terms</a></li>
                            <li class="mb-2"><a href="#intellectual-property" class="text-decoration-none">Intellectual Property</a></li>
                            <li class="mb-2"><a href="#warranty" class="text-decoration-none">Warranties & Limitations</a></li>
                            <li class="mb-2"><a href="#termination" class="text-decoration-none">Termination</a></li>
                            <li class="mb-2"><a href="#governing-law" class="text-decoration-none">Governing Law</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> Please read these terms carefully before using Midas Portal. By accessing or using our service, you agree to be bound by these terms.
                </div>

                <div id="acceptance" class="mb-5">
                    <h2 class="mb-4">1. Acceptance of Terms</h2>
                    <p>These Terms of Service ("Terms") constitute a legally binding agreement between you ("User," "you," or "your") and WebMonks Technologies ("Company," "we," "us," or "our") regarding your use of the Midas Portal insurance management platform ("Service").</p>
                    <p>By accessing or using the Service, you acknowledge that you have read, understood, and agree to be bound by these Terms. If you do not agree to these Terms, you must not access or use the Service.</p>
                </div>

                <div id="services" class="mb-5">
                    <h2 class="mb-4">2. Services Description</h2>
                    <p>Midas Portal provides cloud-based insurance management software designed for insurance agencies and brokers. Our Service includes:</p>
                    <ul>
                        <li>Customer and policy management</li>
                        <li>Claims processing and tracking</li>
                        <li>Quotation and proposal generation</li>
                        <li>WhatsApp integration for customer communication</li>
                        <li>Analytics and reporting tools</li>
                        <li>Document management and storage</li>
                        <li>Commission tracking and calculations</li>
                    </ul>
                    <p>We reserve the right to modify, suspend, or discontinue any aspect of the Service at any time, with or without notice.</p>
                </div>

                <div id="account" class="mb-5">
                    <h2 class="mb-4">3. Account Registration and Security</h2>

                    <h5>3.1 Registration Requirements</h5>
                    <p>To use the Service, you must:</p>
                    <ul>
                        <li>Be at least 18 years old</li>
                        <li>Provide accurate, current, and complete registration information</li>
                        <li>Have the legal authority to bind your organization (if registering on behalf of a company)</li>
                        <li>Comply with all applicable laws and regulations</li>
                    </ul>

                    <h5 class="mt-4">3.2 Account Security</h5>
                    <p>You are responsible for:</p>
                    <ul>
                        <li>Maintaining the confidentiality of your account credentials</li>
                        <li>All activities that occur under your account</li>
                        <li>Notifying us immediately of any unauthorized access or security breach</li>
                        <li>Implementing appropriate security measures (e.g., strong passwords, two-factor authentication)</li>
                    </ul>

                    <h5 class="mt-4">3.3 Account Information</h5>
                    <p>You agree to keep your account information accurate and up-to-date. Failure to do so may result in suspension or termination of your account.</p>
                </div>

                <div id="usage" class="mb-5">
                    <h2 class="mb-4">4. Acceptable Use Policy</h2>

                    <h5>4.1 Permitted Use</h5>
                    <p>You may use the Service only for lawful purposes in accordance with these Terms and all applicable laws.</p>

                    <h5 class="mt-4">4.2 Prohibited Activities</h5>
                    <p>You agree not to:</p>
                    <ul>
                        <li>Violate any applicable laws or regulations</li>
                        <li>Infringe upon intellectual property rights</li>
                        <li>Upload malware, viruses, or harmful code</li>
                        <li>Attempt to gain unauthorized access to systems or accounts</li>
                        <li>Interfere with or disrupt the Service</li>
                        <li>Use automated systems (bots, scrapers) without permission</li>
                        <li>Reverse engineer or decompile the Service</li>
                        <li>Resell or redistribute the Service without authorization</li>
                        <li>Use the Service to send spam or unsolicited communications</li>
                        <li>Impersonate others or provide false information</li>
                    </ul>

                    <div class="alert alert-danger mt-3">
                        <strong>Violation of these terms may result in immediate suspension or termination of your account without refund.</strong>
                    </div>
                </div>

                <div id="payment" class="mb-5">
                    <h2 class="mb-4">5. Payment Terms</h2>

                    <h5>5.1 Subscription Fees</h5>
                    <p>Access to the Service requires payment of subscription fees as outlined in your selected plan. Fees are billed in advance on a monthly or annual basis.</p>

                    <h5 class="mt-4">5.2 Payment Methods</h5>
                    <p>We accept payment via credit card, debit card, net banking, and other approved payment methods. You authorize us to charge your payment method for all fees due.</p>

                    <h5 class="mt-4">5.3 Auto-Renewal</h5>
                    <p>Subscriptions automatically renew at the end of each billing period unless you cancel before the renewal date. You will be charged at the then-current rates.</p>

                    <h5 class="mt-4">5.4 Price Changes</h5>
                    <p>We reserve the right to modify subscription fees with 30 days' advance notice. Changes will take effect upon your next renewal.</p>

                    <h5 class="mt-4">5.5 Refund Policy</h5>
                    <p>Subscription fees are non-refundable except:</p>
                    <ul>
                        <li>Within 14 days of initial subscription (trial period)</li>
                        <li>As required by applicable law</li>
                        <li>At our discretion for service failures or billing errors</li>
                    </ul>

                    <h5 class="mt-4">5.6 Taxes</h5>
                    <p>Fees do not include applicable taxes, which will be added to your invoice as required by law.</p>
                </div>

                <div id="intellectual-property" class="mb-5">
                    <h2 class="mb-4">6. Intellectual Property Rights</h2>

                    <h5>6.1 Our Rights</h5>
                    <p>The Service and all related technology, software, designs, trademarks, and content are owned by or licensed to us. These Terms do not grant you any ownership rights.</p>

                    <h5 class="mt-4">6.2 Your Data</h5>
                    <p>You retain all rights to the data you upload to the Service. You grant us a limited license to use, store, and process your data solely to provide the Service.</p>

                    <h5 class="mt-4">6.3 Feedback</h5>
                    <p>If you provide feedback or suggestions about the Service, we may use them without any obligation to compensate you.</p>
                </div>

                <div id="data-protection" class="mb-5">
                    <h2 class="mb-4">7. Data Protection and Privacy</h2>
                    <p>Your use of the Service is also governed by our <a href="{{ url('/privacy') }}">Privacy Policy</a>, which is incorporated into these Terms by reference.</p>

                    <h5 class="mt-4">7.1 Data Processing</h5>
                    <p>We process your data in accordance with applicable data protection laws, including GDPR and India's data protection regulations.</p>

                    <h5 class="mt-4">7.2 Data Security</h5>
                    <p>While we implement industry-standard security measures, no system is completely secure. You acknowledge that you use the Service at your own risk.</p>
                </div>

                <div id="warranty" class="mb-5">
                    <h2 class="mb-4">8. Warranties and Limitations</h2>

                    <h5>8.1 Service "As Is"</h5>
                    <p>THE SERVICE IS PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, OR NON-INFRINGEMENT.</p>

                    <h5 class="mt-4">8.2 Service Availability</h5>
                    <p>We strive for 99.9% uptime but do not guarantee uninterrupted or error-free service. We are not liable for downtime, data loss, or service interruptions.</p>

                    <h5 class="mt-4">8.3 Limitation of Liability</h5>
                    <p>TO THE MAXIMUM EXTENT PERMITTED BY LAW, WE SHALL NOT BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, OR ANY LOSS OF PROFITS OR REVENUES, WHETHER DIRECT OR INDIRECT, OR ANY LOSS OF DATA, USE, GOODWILL, OR OTHER INTANGIBLE LOSSES.</p>

                    <p class="mt-3">OUR TOTAL LIABILITY SHALL NOT EXCEED THE AMOUNT YOU PAID US IN THE 12 MONTHS PRECEDING THE CLAIM.</p>
                </div>

                <div id="indemnification" class="mb-5">
                    <h2 class="mb-4">9. Indemnification</h2>
                    <p>You agree to indemnify and hold harmless WebMonks Technologies, its officers, directors, employees, and agents from any claims, damages, losses, liabilities, and expenses (including legal fees) arising from:</p>
                    <ul>
                        <li>Your use or misuse of the Service</li>
                        <li>Your violation of these Terms</li>
                        <li>Your violation of any rights of another party</li>
                        <li>Your data or content uploaded to the Service</li>
                    </ul>
                </div>

                <div id="termination" class="mb-5">
                    <h2 class="mb-4">10. Termination</h2>

                    <h5>10.1 Termination by You</h5>
                    <p>You may terminate your account at any time by contacting customer support. Termination will be effective at the end of your current billing period.</p>

                    <h5 class="mt-4">10.2 Termination by Us</h5>
                    <p>We may suspend or terminate your account immediately if you:</p>
                    <ul>
                        <li>Violate these Terms</li>
                        <li>Fail to pay subscription fees</li>
                        <li>Engage in fraudulent or illegal activities</li>
                        <li>Pose a security risk to the Service or other users</li>
                    </ul>

                    <h5 class="mt-4">10.3 Effect of Termination</h5>
                    <p>Upon termination:</p>
                    <ul>
                        <li>Your access to the Service will cease</li>
                        <li>You must cease all use of the Service</li>
                        <li>We may delete your data after 30 days (export data before termination)</li>
                        <li>Provisions that by their nature should survive will continue (e.g., limitations of liability, indemnification)</li>
                    </ul>
                </div>

                <div id="modifications" class="mb-5">
                    <h2 class="mb-4">11. Modifications to Terms</h2>
                    <p>We may modify these Terms at any time by posting updated terms on our website. Material changes will be notified via email or platform notification at least 30 days before taking effect.</p>
                    <p>Your continued use of the Service after changes become effective constitutes acceptance of the modified Terms.</p>
                </div>

                <div id="governing-law" class="mb-5">
                    <h2 class="mb-4">12. Governing Law and Dispute Resolution</h2>

                    <h5>12.1 Governing Law</h5>
                    <p>These Terms shall be governed by and construed in accordance with the laws of India, without regard to its conflict of law provisions.</p>

                    <h5 class="mt-4">12.2 Jurisdiction</h5>
                    <p>Any disputes arising from these Terms or your use of the Service shall be subject to the exclusive jurisdiction of the courts of Ahmedabad, Gujarat, India.</p>

                    <h5 class="mt-4">12.3 Dispute Resolution</h5>
                    <p>Before filing any legal action, you agree to attempt to resolve disputes through good-faith negotiation with us for at least 30 days.</p>
                </div>

                <div id="general" class="mb-5">
                    <h2 class="mb-4">13. General Provisions</h2>

                    <h5>13.1 Entire Agreement</h5>
                    <p>These Terms, together with our Privacy Policy and any other legal notices published by us, constitute the entire agreement between you and us.</p>

                    <h5 class="mt-4">13.2 Severability</h5>
                    <p>If any provision of these Terms is found to be invalid or unenforceable, the remaining provisions will continue in full force and effect.</p>

                    <h5 class="mt-4">13.3 Waiver</h5>
                    <p>Our failure to enforce any right or provision shall not be deemed a waiver of such right or provision.</p>

                    <h5 class="mt-4">13.4 Assignment</h5>
                    <p>You may not assign or transfer these Terms without our written consent. We may assign these Terms without restriction.</p>

                    <h5 class="mt-4">13.5 Force Majeure</h5>
                    <p>We shall not be liable for any failure or delay in performance due to circumstances beyond our reasonable control.</p>
                </div>

                <div id="contact" class="mb-5">
                    <h2 class="mb-4">14. Contact Information</h2>
                    <p>If you have questions about these Terms, please contact us:</p>

                    <div class="card border-primary">
                        <div class="card-body">
                            <p class="mb-2"><strong>WebMonks Technologies</strong></p>
                            <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>C243, Second Floor, SoBo Center, Gala Gymkhana Road, South Bopal, Ahmedabad - 380058, Gujarat, India</p>
                            <p class="mb-2"><i class="fas fa-envelope me-2"></i><a href="mailto:Info@midastech.in">Info@midastech.in</a></p>
                            <p class="mb-0"><i class="fas fa-phone me-2"></i>+91 80000 71413</p>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-file-contract me-2"></i>
                    <strong>Acceptance:</strong> By using Midas Portal, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
