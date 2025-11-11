@extends('public.layout')

@section('title', 'Security - Data Protection & Infrastructure Security | Midas Portal')
@section('meta_description', 'Learn about Midas Portal security measures, data protection, encryption, compliance, and infrastructure security.')
@section('meta_keywords', 'security, data protection, encryption, compliance, infrastructure security, cybersecurity')

@section('content')
<!-- Page Header -->
<div style="background: var(--gradient-primary); color: white; padding: 60px 0;">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3"><i class="fas fa-shield-alt me-3"></i>Security</h1>
        <p class="lead">Enterprise-grade security to protect your insurance data</p>
        <div class="mt-4">
            <span class="badge bg-light text-dark me-2">ISO 27001 Ready</span>
            <span class="badge bg-light text-dark me-2">SOC 2 Type II Compliant</span>
            <span class="badge bg-light text-dark">GDPR Compliant</span>
        </div>
    </div>
</div>

<!-- Security Overview -->
<section class="py-5">
    <div class="container text-center">
        <h2 class="mb-4">Security is Our Top Priority</h2>
        <p class="lead text-muted mb-5">We implement multiple layers of security to protect your sensitive insurance data</p>

        <div class="row g-4">
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="feature-icon mx-auto mb-3">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h4 class="text-primary">AES-256</h4>
                        <p class="text-muted small">Data Encryption</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="feature-icon mx-auto mb-3">
                            <i class="fas fa-server"></i>
                        </div>
                        <h4 class="text-primary">99.9%</h4>
                        <p class="text-muted small">Uptime SLA</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="feature-icon mx-auto mb-3">
                            <i class="fas fa-database"></i>
                        </div>
                        <h4 class="text-primary">Daily</h4>
                        <p class="text-muted small">Automated Backups</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="feature-icon mx-auto mb-3">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h4 class="text-primary">24/7</h4>
                        <p class="text-muted small">Security Monitoring</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Data Security -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Data Security</h2>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100 border-success">
                    <div class="card-body">
                        <h5><i class="fas fa-lock text-success me-2"></i>Encryption at Rest</h5>
                        <p>All data stored in our databases is encrypted using AES-256 encryption, the same standard used by banks and government agencies.</p>
                        <ul class="small">
                            <li>Database-level encryption</li>
                            <li>File storage encryption</li>
                            <li>Backup encryption</li>
                            <li>Secure key management</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 border-success">
                    <div class="card-body">
                        <h5><i class="fas fa-exchange-alt text-success me-2"></i>Encryption in Transit</h5>
                        <p>All data transmitted between your browser and our servers is protected using TLS 1.3, ensuring secure communication.</p>
                        <ul class="small">
                            <li>TLS 1.3 encryption</li>
                            <li>Perfect forward secrecy</li>
                            <li>Strong cipher suites</li>
                            <li>HTTPS enforcement</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 border-success">
                    <div class="card-body">
                        <h5><i class="fas fa-users-cog text-success me-2"></i>Multi-Tenant Isolation</h5>
                        <p>Complete data separation ensures that each insurance agency's data is isolated and inaccessible to others.</p>
                        <ul class="small">
                            <li>Logical data isolation</li>
                            <li>Tenant-specific databases</li>
                            <li>Access control enforcement</li>
                            <li>Cross-tenant protection</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 border-success">
                    <div class="card-body">
                        <h5><i class="fas fa-database text-success me-2"></i>Secure Backups</h5>
                        <p>Automated daily backups with point-in-time recovery capabilities ensure your data is never lost.</p>
                        <ul class="small">
                            <li>Automated daily backups</li>
                            <li>Encrypted backup storage</li>
                            <li>30-day retention period</li>
                            <li>Disaster recovery procedures</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Access Control -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Access Control & Authentication</h2>
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h4>Role-Based Access Control</h4>
                <p>Granular permission system ensures users only access what they need:</p>
                <ul>
                    <li><strong>Admin:</strong> Full system access and configuration</li>
                    <li><strong>Manager:</strong> Team oversight and reporting</li>
                    <li><strong>Agent:</strong> Customer and policy management</li>
                    <li><strong>Support:</strong> Limited view-only access</li>
                </ul>

                <h4 class="mt-4">Multi-Factor Authentication</h4>
                <p>Additional security layer beyond passwords:</p>
                <ul>
                    <li>SMS-based verification</li>
                    <li>Email-based verification</li>
                    <li>Time-based one-time passwords (TOTP)</li>
                    <li>Backup recovery codes</li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="card border-primary">
                    <div class="card-body">
                        <h5 class="card-title">Additional Security Features</h5>
                        <ul class="mb-0">
                            <li>Strong password requirements</li>
                            <li>Automatic session timeout</li>
                            <li>IP whitelisting (Enterprise)</li>
                            <li>Single Sign-On (SSO) integration</li>
                            <li>Failed login attempt monitoring</li>
                            <li>Account lockout protection</li>
                            <li>Password reset verification</li>
                            <li>Audit logs for all access</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Infrastructure Security -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Infrastructure Security</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-cloud text-primary mb-3" style="font-size: 3rem;"></i>
                        <h5>Cloud Infrastructure</h5>
                        <p class="text-muted">Hosted on enterprise-grade cloud infrastructure with built-in security and redundancy</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-fire-extinguisher text-primary mb-3" style="font-size: 3rem;"></i>
                        <h5>Firewall Protection</h5>
                        <p class="text-muted">Web application firewall (WAF) and DDoS protection to prevent attacks</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-bug text-primary mb-3" style="font-size: 3rem;"></i>
                        <h5>Intrusion Detection</h5>
                        <p class="text-muted">Real-time monitoring and automated threat detection systems</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-sync text-primary mb-3" style="font-size: 3rem;"></i>
                        <h5>Regular Updates</h5>
                        <p class="text-muted">Continuous security patches and updates to address vulnerabilities</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-network-wired text-primary mb-3" style="font-size: 3rem;"></i>
                        <h5>Network Isolation</h5>
                        <p class="text-muted">Segregated networks and private subnets for enhanced security</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-file-alt text-primary mb-3" style="font-size: 3rem;"></i>
                        <h5>Activity Logging</h5>
                        <p class="text-muted">Comprehensive audit trails for compliance and security analysis</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Compliance -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Compliance & Certifications</h2>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                        <h5>GDPR</h5>
                        <p class="text-muted small">General Data Protection Regulation compliance</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                        <h5>ISO 27001</h5>
                        <p class="text-muted small">Information security management standards</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                        <h5>SOC 2 Type II</h5>
                        <p class="text-muted small">Service organization control compliance</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                        <h5>IRDAI Guidelines</h5>
                        <p class="text-muted small">Insurance regulatory compliance (India)</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info mt-5">
            <h5><i class="fas fa-info-circle me-2"></i>Data Residency</h5>
            <p class="mb-0">All data is stored in secure data centers located in India, ensuring compliance with local data protection regulations.</p>
        </div>
    </div>
</section>

<!-- Security Practices -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Our Security Practices</h2>
        <div class="row">
            <div class="col-lg-6">
                <h4>Development Security</h4>
                <ul>
                    <li><strong>Secure Coding:</strong> Following OWASP Top 10 security guidelines</li>
                    <li><strong>Code Reviews:</strong> Peer review for all code changes</li>
                    <li><strong>Dependency Scanning:</strong> Automated vulnerability scanning</li>
                    <li><strong>Static Analysis:</strong> Automated security testing</li>
                </ul>

                <h4 class="mt-4">Testing & Validation</h4>
                <ul>
                    <li><strong>Penetration Testing:</strong> Regular third-party security audits</li>
                    <li><strong>Vulnerability Assessments:</strong> Quarterly security scans</li>
                    <li><strong>Security Testing:</strong> Automated and manual testing</li>
                    <li><strong>Bug Bounty Program:</strong> Responsible disclosure program</li>
                </ul>
            </div>
            <div class="col-lg-6">
                <h4>Operational Security</h4>
                <ul>
                    <li><strong>24/7 Monitoring:</strong> Real-time security monitoring</li>
                    <li><strong>Incident Response:</strong> Dedicated security team</li>
                    <li><strong>Disaster Recovery:</strong> Tested recovery procedures</li>
                    <li><strong>Business Continuity:</strong> Comprehensive continuity plans</li>
                </ul>

                <h4 class="mt-4">Employee Security</h4>
                <ul>
                    <li><strong>Background Checks:</strong> For all employees</li>
                    <li><strong>Security Training:</strong> Regular security awareness training</li>
                    <li><strong>Access Controls:</strong> Least privilege principle</li>
                    <li><strong>NDAs:</strong> Confidentiality agreements</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Vulnerability Disclosure -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-warning">
                    <div class="card-body">
                        <h4><i class="fas fa-exclamation-triangle text-warning me-2"></i>Responsible Disclosure</h4>
                        <p>If you discover a security vulnerability, please report it responsibly:</p>
                        <ul>
                            <li>Email us at <a href="mailto:security@midastech.in">security@midastech.in</a></li>
                            <li>Provide detailed information about the vulnerability</li>
                            <li>Allow us reasonable time to address the issue</li>
                            <li>Do not disclose publicly until we've resolved it</li>
                        </ul>
                        <p class="mb-0">We appreciate responsible security researchers and will acknowledge your contribution.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Security Questions -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <h2 class="mb-4">Have Security Questions?</h2>
        <p class="text-muted mb-4">Our security team is here to address your concerns</p>
        <a href="{{ url('/contact') }}" class="btn btn-primary btn-lg">Contact Security Team</a>
    </div>
</section>
@endsection
