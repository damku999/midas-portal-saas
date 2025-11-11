@extends('public.layout')

@section('title', 'Privacy Policy - Data Protection & Privacy | Midas Portal')
@section('meta_description', 'Midas Portal privacy policy. Learn how we collect, use, and protect your personal information and insurance data.')
@section('meta_keywords', 'privacy policy, data protection, gdpr compliance, data security, personal information')

@section('content')
<!-- Page Header -->
<div style="background: var(--gradient-primary); color: white; padding: 60px 0;">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">Privacy Policy</h1>
        <p class="lead">Your privacy and data security are our top priorities</p>
        <p class="small">Last Updated: December 15, 2024</p>
    </div>
</div>

<!-- Privacy Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h6 class="text-muted mb-3">TABLE OF CONTENTS</h6>
                        <ul class="list-unstyled small">
                            <li class="mb-2"><a href="#introduction" class="text-decoration-none">Introduction</a></li>
                            <li class="mb-2"><a href="#information-collection" class="text-decoration-none">Information We Collect</a></li>
                            <li class="mb-2"><a href="#how-we-use" class="text-decoration-none">How We Use Information</a></li>
                            <li class="mb-2"><a href="#data-sharing" class="text-decoration-none">Data Sharing</a></li>
                            <li class="mb-2"><a href="#data-security" class="text-decoration-none">Data Security</a></li>
                            <li class="mb-2"><a href="#your-rights" class="text-decoration-none">Your Rights</a></li>
                            <li class="mb-2"><a href="#cookies" class="text-decoration-none">Cookies</a></li>
                            <li class="mb-2"><a href="#contact" class="text-decoration-none">Contact Us</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div id="introduction" class="mb-5">
                    <h2 class="mb-4">Introduction</h2>
                    <p>Welcome to Midas Portal ("we," "our," or "us"). We respect your privacy and are committed to protecting your personal data. This privacy policy explains how we collect, use, disclose, and safeguard your information when you use our insurance management platform.</p>
                    <p>This policy applies to all users of Midas Portal, including insurance agencies, their staff members, and end customers who access our platform.</p>
                </div>

                <div id="information-collection" class="mb-5">
                    <h2 class="mb-4">Information We Collect</h2>

                    <h5>Personal Information</h5>
                    <p>We collect information that you provide directly to us, including:</p>
                    <ul>
                        <li>Name, email address, phone number</li>
                        <li>Business information (company name, address, tax ID)</li>
                        <li>Customer data (insurance policy details, claims information)</li>
                        <li>Payment and billing information</li>
                        <li>Communication preferences</li>
                    </ul>

                    <h5 class="mt-4">Automatically Collected Information</h5>
                    <p>When you use our platform, we automatically collect:</p>
                    <ul>
                        <li>Device information (IP address, browser type, operating system)</li>
                        <li>Usage data (pages visited, features used, time spent)</li>
                        <li>Log data (access times, error logs)</li>
                        <li>Cookies and similar tracking technologies</li>
                    </ul>

                    <h5 class="mt-4">Insurance-Related Data</h5>
                    <p>As an insurance management platform, we process:</p>
                    <ul>
                        <li>Policy information (coverage details, premium amounts, policy terms)</li>
                        <li>Claims data (incident details, supporting documents, claim status)</li>
                        <li>Customer information (personal details, family members, contact preferences)</li>
                        <li>Financial transactions (premium payments, commission calculations)</li>
                    </ul>
                </div>

                <div id="how-we-use" class="mb-5">
                    <h2 class="mb-4">How We Use Your Information</h2>
                    <p>We use the collected information for the following purposes:</p>

                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <div class="card h-100 border-primary">
                                <div class="card-body">
                                    <h6><i class="fas fa-cog text-primary me-2"></i>Service Delivery</h6>
                                    <ul class="small mb-0">
                                        <li>Provide and maintain our services</li>
                                        <li>Process insurance policies and claims</li>
                                        <li>Send notifications and reminders</li>
                                        <li>Customer support and communication</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-primary">
                                <div class="card-body">
                                    <h6><i class="fas fa-chart-line text-primary me-2"></i>Platform Improvement</h6>
                                    <ul class="small mb-0">
                                        <li>Analyze usage patterns</li>
                                        <li>Improve platform features</li>
                                        <li>Develop new functionality</li>
                                        <li>Enhance user experience</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-primary">
                                <div class="card-body">
                                    <h6><i class="fas fa-shield-alt text-primary me-2"></i>Security & Compliance</h6>
                                    <ul class="small mb-0">
                                        <li>Prevent fraud and abuse</li>
                                        <li>Ensure platform security</li>
                                        <li>Comply with legal obligations</li>
                                        <li>Enforce terms and policies</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-primary">
                                <div class="card-body">
                                    <h6><i class="fas fa-envelope text-primary me-2"></i>Communication</h6>
                                    <ul class="small mb-0">
                                        <li>Send service updates</li>
                                        <li>Respond to inquiries</li>
                                        <li>Marketing communications (with consent)</li>
                                        <li>Product announcements</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="data-sharing" class="mb-5">
                    <h2 class="mb-4">Data Sharing and Disclosure</h2>
                    <p>We do not sell your personal information. We may share your data in the following circumstances:</p>

                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Service Providers</h6>
                        <p class="mb-0">We work with third-party service providers for hosting, payment processing, email delivery, and analytics. These providers are contractually obligated to protect your data.</p>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Legal Requirements</h6>
                        <p class="mb-0">We may disclose information when required by law, court order, or government regulation, or to protect our rights and safety.</p>
                    </div>

                    <div class="alert alert-success">
                        <h6><i class="fas fa-handshake me-2"></i>Business Transfers</h6>
                        <p class="mb-0">In the event of a merger, acquisition, or sale of assets, your information may be transferred to the acquiring entity.</p>
                    </div>
                </div>

                <div id="data-security" class="mb-5">
                    <h2 class="mb-4">Data Security</h2>
                    <p>We implement industry-standard security measures to protect your information:</p>

                    <ul>
                        <li><strong>Encryption:</strong> All data is encrypted in transit (TLS 1.3) and at rest (AES-256)</li>
                        <li><strong>Multi-Tenant Isolation:</strong> Complete data separation between insurance agencies</li>
                        <li><strong>Access Controls:</strong> Role-based permissions and two-factor authentication</li>
                        <li><strong>Regular Backups:</strong> Automated daily backups with disaster recovery procedures</li>
                        <li><strong>Security Audits:</strong> Regular vulnerability assessments and penetration testing</li>
                        <li><strong>Monitoring:</strong> 24/7 security monitoring and incident response</li>
                    </ul>
                </div>

                <div id="your-rights" class="mb-5">
                    <h2 class="mb-4">Your Privacy Rights</h2>
                    <p>You have the following rights regarding your personal information:</p>

                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td><strong>Access</strong></td>
                                <td>Request a copy of your personal data we hold</td>
                            </tr>
                            <tr>
                                <td><strong>Correction</strong></td>
                                <td>Request correction of inaccurate or incomplete data</td>
                            </tr>
                            <tr>
                                <td><strong>Deletion</strong></td>
                                <td>Request deletion of your personal data (subject to legal obligations)</td>
                            </tr>
                            <tr>
                                <td><strong>Portability</strong></td>
                                <td>Receive your data in a machine-readable format</td>
                            </tr>
                            <tr>
                                <td><strong>Objection</strong></td>
                                <td>Object to processing of your data for specific purposes</td>
                            </tr>
                            <tr>
                                <td><strong>Restriction</strong></td>
                                <td>Request restriction of processing in certain circumstances</td>
                            </tr>
                            <tr>
                                <td><strong>Withdraw Consent</strong></td>
                                <td>Withdraw consent for marketing communications at any time</td>
                            </tr>
                        </tbody>
                    </table>

                    <p>To exercise these rights, please contact us at <a href="mailto:privacy@midastech.in">privacy@midastech.in</a></p>
                </div>

                <div id="cookies" class="mb-5">
                    <h2 class="mb-4">Cookies and Tracking Technologies</h2>
                    <p>We use cookies and similar technologies to enhance your experience:</p>

                    <ul>
                        <li><strong>Essential Cookies:</strong> Required for platform functionality and security</li>
                        <li><strong>Performance Cookies:</strong> Help us understand how you use our platform</li>
                        <li><strong>Functional Cookies:</strong> Remember your preferences and settings</li>
                        <li><strong>Analytics Cookies:</strong> Provide insights into platform usage and performance</li>
                    </ul>

                    <p>You can manage cookie preferences through your browser settings. Note that disabling certain cookies may affect platform functionality.</p>
                </div>

                <div id="data-retention" class="mb-5">
                    <h2 class="mb-4">Data Retention</h2>
                    <p>We retain your information for as long as necessary to:</p>
                    <ul>
                        <li>Provide our services to you</li>
                        <li>Comply with legal and regulatory requirements</li>
                        <li>Resolve disputes and enforce agreements</li>
                        <li>Maintain business records and analytics</li>
                    </ul>
                    <p>Insurance-related data is retained according to industry regulations and legal requirements, typically 7-10 years after policy expiration.</p>
                </div>

                <div id="international-transfers" class="mb-5">
                    <h2 class="mb-4">International Data Transfers</h2>
                    <p>Your information may be transferred to and processed in countries other than India. We ensure appropriate safeguards are in place for such transfers, including:</p>
                    <ul>
                        <li>Standard contractual clauses approved by regulatory authorities</li>
                        <li>Adequacy decisions by competent authorities</li>
                        <li>Privacy Shield or equivalent frameworks where applicable</li>
                    </ul>
                </div>

                <div id="children" class="mb-5">
                    <h2 class="mb-4">Children's Privacy</h2>
                    <p>Midas Portal is not intended for children under 18. We do not knowingly collect personal information from children. If you believe we have collected information from a child, please contact us immediately.</p>
                </div>

                <div id="changes" class="mb-5">
                    <h2 class="mb-4">Changes to This Privacy Policy</h2>
                    <p>We may update this privacy policy periodically. We will notify you of significant changes via email or platform notification. The "Last Updated" date at the top indicates when the policy was last revised.</p>
                </div>

                <div id="contact" class="mb-5">
                    <h2 class="mb-4">Contact Us</h2>
                    <p>If you have questions or concerns about this privacy policy or our data practices, please contact us:</p>

                    <div class="card border-primary">
                        <div class="card-body">
                            <p class="mb-2"><strong>WebMonks Technologies</strong></p>
                            <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>C243, Second Floor, SoBo Center, Gala Gymkhana Road, South Bopal, Ahmedabad - 380058, Gujarat, India</p>
                            <p class="mb-2"><i class="fas fa-envelope me-2"></i><a href="mailto:privacy@midastech.in">privacy@midastech.in</a></p>
                            <p class="mb-0"><i class="fas fa-phone me-2"></i>+91 80000 71413</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
