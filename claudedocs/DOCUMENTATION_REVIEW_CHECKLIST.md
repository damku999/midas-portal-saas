# Quarterly Documentation Review Checklist

**Purpose**: Systematic review process to ensure documentation remains accurate and synchronized with codebase
**Frequency**: Every 3 months (Q1: January, Q2: April, Q3: July, Q4: October)
**Owner**: Technical Lead or Documentation Maintainer
**Time Required**: 4-6 hours

---

## Pre-Review Preparation

### Gather Information

- [ ] Generate current route list: `php artisan route:list --json > routes.json`
- [ ] Get model count: `find app/Models -name "*.php" | wc -l`
- [ ] Count migrations: `find database/migrations -name "*.php" | wc -l`
- [ ] List services: `find app/Services -name "*Service.php"`
- [ ] List middleware: Check `app/Http/Kernel.php`
- [ ] List Artisan commands: `php artisan list --format=json > commands.json`

### Review Recent Changes

- [ ] Review git commits since last review: `git log --since="3 months ago" --oneline`
- [ ] Check closed issues/PRs: Review GitHub/GitLab for feature additions
- [ ] Review release notes for past quarter
- [ ] Identify deprecated features or breaking changes

---

## Section 1: Core Architecture Documentation

**Files**: `core/` directory (6 files)

### DATABASE_SCHEMA.md

- [ ] Verify migration count matches actual migrations (currently: 72)
- [ ] Check for new tables not documented
- [ ] Verify all ER diagrams are current
- [ ] Test sample queries in documentation
- [ ] Update table count in summary
- [ ] Check indexes documentation is complete
- [ ] Verify foreign key relationships accurate

**Commands**:
```bash
# Count migrations
ls database/migrations/*.php | wc -l

# List tables not in schema doc
php artisan tinker
>>> Schema::getTables()
# Compare with DATABASE_SCHEMA.md
```

### SERVICE_LAYER.md

- [ ] Verify service count matches actual (currently: 51)
- [ ] Check for new services in `app/Services/`
- [ ] Test code examples for each service
- [ ] Update dependency graph if new dependencies
- [ ] Verify method signatures match actual code
- [ ] Check for deprecated services

**Commands**:
```bash
# Count services
find app/Services -name "*Service.php" | wc -l

# List all services
find app/Services -name "*Service.php" -exec basename {} \;
```

### MIDDLEWARE_REFERENCE.md

- [ ] Verify middleware count matches (currently: 21)
- [ ] Check `app/Http/Kernel.php` for new middleware
- [ ] Verify execution order examples
- [ ] Test middleware stack examples
- [ ] Update middleware group definitions
- [ ] Check for removed middleware

**Commands**:
```bash
# Check middleware registration
grep -A 50 "protected \$middleware" app/Http/Kernel.php
```

### ARTISAN_COMMANDS.md

- [ ] Verify command count (currently: 11)
- [ ] Check for new commands: `php artisan list`
- [ ] Test command execution examples
- [ ] Verify scheduler configuration
- [ ] Update command output examples
- [ ] Check for deprecated commands

**Commands**:
```bash
# List custom commands
php artisan list | grep -v "Laravel"

# Check scheduler
php artisan schedule:list
```

### MULTI_PORTAL_ARCHITECTURE.md

- [ ] Verify portal count and descriptions
- [ ] Check routing examples match `routes/web.php`
- [ ] Verify authentication flows
- [ ] Test middleware stack examples
- [ ] Check domain configuration

**Commands**:
```bash
# Check route groups
grep -n "Route::" routes/web.php | head -20
```

### ARTISAN_COMMANDS.md

- [ ] Update "Last Updated" date
- [ ] Increment version if major changes

---

## Section 2: Feature Documentation

**Files**: `features/` directory (12 files)

### For Each Feature File:

- [ ] Verify feature still exists and is active
- [ ] Test all code examples
- [ ] Check API endpoints documented match actual routes
- [ ] Verify configuration options current
- [ ] Update screenshots if UI changed
- [ ] Check for new capabilities to document
- [ ] Verify integration points

### SUBSCRIPTION_MANAGEMENT.md

- [ ] Verify plan details (pricing, limits)
- [ ] Check trial period configuration (DEFAULT_TRIAL_DAYS)
- [ ] Test subscription creation examples
- [ ] Verify usage tracking formulas
- [ ] Check limit enforcement logic

### PAYMENT_GATEWAY_INTEGRATION.md

- [ ] Verify Razorpay/Stripe API versions
- [ ] Test webhook endpoints
- [ ] Check test card numbers still valid
- [ ] Verify refund process
- [ ] Update gateway feature additions

### All Other Feature Files

- [ ] TWO_FACTOR_AUTHENTICATION.md - TOTP implementation current
- [ ] DEVICE_TRACKING.md - Fingerprinting logic accurate
- [ ] AUDIT_LOGGING.md - Event types complete
- [ ] NOTIFICATION_SYSTEM.md - All channels documented
- [ ] COMMISSION_TRACKING.md - Calculation formulas correct
- [ ] FAMILY_GROUP_MANAGEMENT.md - Relationships accurate
- [ ] PDF_GENERATION.md - Template paths correct
- [ ] APP_SETTINGS.md - Setting categories complete
- [ ] FILE_STORAGE_MULTI_TENANCY.md - Quota limits current
- [ ] TRIAL_CONVERSION_SYSTEM.md - Automation working

---

## Section 3: Module Documentation

**Files**: `modules/` directory (7 files)

### For Each Module File:

- [ ] Verify business workflows accurate
- [ ] Test database queries and examples
- [ ] Check model relationships
- [ ] Verify validation rules
- [ ] Test API endpoint examples
- [ ] Update numbering formats if changed

### Specific Checks:

- [ ] CUSTOMER_MANAGEMENT.md - Family group logic
- [ ] LEAD_MANAGEMENT.md - Lead numbering format, WhatsApp integration
- [ ] POLICY_MANAGEMENT.md - NCB scale, commission calculations
- [ ] QUOTATION_SYSTEM.md - IDV formulas, addon covers
- [ ] CLAIMS_MANAGEMENT.md - Document types, workflow stages
- [ ] USER_ROLE_MANAGEMENT.md - Permission list complete
- [ ] MASTER_DATA.md - Seeder data current

---

## Section 4: API Documentation

**File**: `API_REFERENCE.md`

### Route Verification

- [ ] Compare documented routes with `php artisan route:list`
- [ ] Current count: 455 routes (verify this number)
- [ ] Check for new endpoints not documented
- [ ] Verify deprecated endpoints marked
- [ ] Update route counts in summary

**Automated Check**:
```bash
# Generate route list
php artisan route:list --columns=uri,method,name > current_routes.txt

# Compare with API_REFERENCE.md manually or with script
```

### Endpoint Testing

- [ ] Test 10 random endpoint examples from documentation
- [ ] Verify request/response formats match
- [ ] Check authentication requirements accurate
- [ ] Verify rate limiting documented
- [ ] Test error responses match documentation

### API Sections

- [ ] Public Website API (10 routes)
- [ ] Central Admin API (60+ routes)
- [ ] Tenant Staff API (300+ routes)
- [ ] Customer Portal API (40+ routes)
- [ ] Subscription Management (8 routes)
- [ ] Payment Webhooks (5 routes)
- [ ] Marketing & Campaigns (3 routes)
- [ ] System Utilities (20+ routes)

---

## Section 5: Setup & Configuration

**Files**: `setup/` directory (3 files)

### ENVIRONMENT_CONFIGURATION.md

- [ ] Verify all .env variables documented (currently: 40+)
- [ ] Check config files count (currently: 30+)
- [ ] Test configuration examples
- [ ] Verify default values current
- [ ] Check for new environment variables

**Commands**:
```bash
# List env vars in .env.example
grep -E "^[A-Z_]+" .env.example | wc -l

# List config files
ls -1 config/*.php | wc -l
```

### LOCAL_TENANT_ACCESS_GUIDE.md

- [ ] Test local setup instructions
- [ ] Verify domain configuration
- [ ] Check ngrok integration if used

### NGROK_QUICK_START.md

- [ ] Verify ngrok version compatibility
- [ ] Test webhook configuration steps

---

## Section 6: Operations & Deployment

**Files**: `operations/` directory (3 files)

### DEPLOYMENT.md

- [ ] Verify server requirements current
- [ ] Test deployment commands
- [ ] Check Nginx configuration
- [ ] Verify SSL setup instructions
- [ ] Test rollback procedures
- [ ] Verify payment gateway setup (Section 6)
- [ ] Check multi-portal configuration (Section 7)

### TROUBLESHOOTING.md

- [ ] Review open issues for new troubleshooting scenarios
- [ ] Test diagnostic commands
- [ ] Verify emergency procedures
- [ ] Update common issues section
- [ ] Check subscription troubleshooting current
- [ ] Verify payment gateway troubleshooting accurate

### MULTI_TENANCY_FIXES.md

- [ ] Verify fixes still relevant
- [ ] Remove fixed issues if resolved permanently
- [ ] Add new known issues

---

## Section 7: General Documentation

### README.md

- [ ] Verify file count (currently: 38 files)
- [ ] Check all links functional
- [ ] Update documentation statistics
- [ ] Verify quick start guides current
- [ ] Update "Last Updated" date

**Link Check**:
```bash
# Install if needed: npm install -g markdown-link-check
markdown-link-check claudedocs/README.md
```

### FEATURES.md

- [ ] Verify all features listed
- [ ] Check links to detailed docs
- [ ] Update feature descriptions if changed
- [ ] Verify system overview accurate

### ARCHITECTURE.md

- [ ] Verify system architecture current
- [ ] Check technology stack versions
- [ ] Update design patterns if changed

---

## Section 8: Cross-References & Links

### Link Validation

- [ ] Test all internal links: `find claudedocs -name "*.md" -exec markdown-link-check {} \;`
- [ ] Fix broken links
- [ ] Verify "Related Documentation" sections present
- [ ] Check bidirectional linking (A→B and B→A)

### Cross-Reference Audit

Sample 5 random files and verify:
- [ ] Related Documentation section exists
- [ ] Links are relevant and helpful
- [ ] Links use relative paths
- [ ] Links include context/description

---

## Section 9: Code Examples & Samples

### Code Example Testing

Select 20 random code examples and verify:
- [ ] Code syntax is valid
- [ ] Examples run without errors
- [ ] Examples use current API/syntax
- [ ] Examples follow best practices
- [ ] Variable names match current conventions

### Database Queries

- [ ] Test 10 SQL queries from documentation
- [ ] Verify Eloquent query examples
- [ ] Check Tinker examples

### Shell Commands

- [ ] Test Artisan command examples
- [ ] Verify bash script examples
- [ ] Check composer commands

---

## Section 10: Version & Metadata

### Version Numbers

- [ ] Verify all files show correct version (currently: 3.0)
- [ ] Update "Last Updated" dates for reviewed files
- [ ] Check version consistency across files

### File Metadata

Check each file has:
- [ ] Title at top
- [ ] Version number
- [ ] Last Updated date
- [ ] Related Documentation section (where applicable)

---

## Section 11: Documentation Quality

### Writing Quality

Sample 10 random files and check:
- [ ] Grammar and spelling correct
- [ ] Technical accuracy verified
- [ ] Terminology consistent
- [ ] Appropriate detail level for audience
- [ ] Clear headings and structure

### Formatting Quality

- [ ] Markdown formatting valid
- [ ] Code blocks have language specified
- [ ] Tables formatted correctly
- [ ] Lists use consistent style
- [ ] Images load correctly (if any)

### Accessibility

- [ ] Headings hierarchical (H1 → H2 → H3)
- [ ] Links descriptive (not "click here")
- [ ] Tables have headers
- [ ] Code blocks readable

---

## Section 12: Documentation Metrics

### Update Statistics

Update these metrics in README.md:

- [ ] Total documents (currently: 38)
- [ ] Total lines (currently: 28,000+)
- [ ] Database tables documented (currently: 45)
- [ ] API endpoints documented (currently: 455)
- [ ] Services documented (currently: 51)
- [ ] Middleware documented (currently: 21)
- [ ] Artisan commands documented (currently: 11)

### Coverage Verification

- [ ] Core Architecture: 100% (6/6 files)
- [ ] Features: 100% (12/12 files)
- [ ] Modules: 100% (7/7 files)
- [ ] API: 100% (all routes)
- [ ] Setup: 100% (3/3 files)
- [ ] Operations: 100% (3/3 files)
- [ ] Testing: 100% (2/2 files)

---

## Post-Review Actions

### Issues Found

For each issue discovered:
- [ ] Create GitHub issue with `documentation` label
- [ ] Assign to appropriate team member
- [ ] Set priority (Critical/High/Medium/Low)
- [ ] Add to sprint if needed

### Updates Required

- [ ] Create list of required updates
- [ ] Prioritize updates
- [ ] Schedule documentation work
- [ ] Create PRs for documentation fixes

### Review Summary

Create review summary document:
- [ ] Date of review
- [ ] Reviewer name
- [ ] Issues found (count)
- [ ] Updates made (count)
- [ ] Files reviewed (count)
- [ ] Time spent
- [ ] Next review date

**Template**:
```markdown
# Documentation Review Summary - Q[X] 2025

**Date**: YYYY-MM-DD
**Reviewer**: [Name]
**Time Spent**: X hours

## Summary
- Files Reviewed: 38/38
- Issues Found: X
- Updates Made: X
- Documentation Health: Excellent/Good/Needs Work

## Issues Found
1. [Issue description]
2. [Issue description]

## Updates Made
1. [Update description]
2. [Update description]

## Recommendations
- [Recommendation]

## Next Review
**Scheduled**: [Date in 3 months]
```

---

## Review Completion

### Final Checks

- [ ] All sections completed
- [ ] All issues documented
- [ ] Updates committed to git
- [ ] Review summary created
- [ ] Next review scheduled
- [ ] Team notified of findings

### Git Commit

```bash
git add claudedocs/
git commit -m "docs: Q[X] 2025 quarterly documentation review

- Reviewed all 38 documentation files
- Updated [X] files with current information
- Fixed [X] broken links
- Updated documentation statistics
- Next review: [Date]
"
git push origin main
```

---

## Automation Opportunities

### Scripts to Create

**1. Route Count Checker** (`scripts/check-routes.sh`):
```bash
#!/bin/bash
ROUTE_COUNT=$(php artisan route:list --json | jq '. | length')
DOC_COUNT=$(grep -c "GET\|POST\|PUT\|DELETE" claudedocs/API_REFERENCE.md)
echo "Actual routes: $ROUTE_COUNT"
echo "Documented routes: $DOC_COUNT"
```

**2. Link Checker** (`scripts/check-links.sh`):
```bash
#!/bin/bash
find claudedocs -name "*.md" -exec markdown-link-check {} \; > link-check-results.txt
```

**3. Version Updater** (`scripts/update-version.sh`):
```bash
#!/bin/bash
NEW_VERSION=$1
find claudedocs -name "*.md" -exec sed -i "s/Version: [0-9.]\+/Version: $NEW_VERSION/g" {} \;
```

---

## Quarterly Review Schedule

**Q1 (January)**: Post-holiday review, plan updates for new year
**Q2 (April)**: Mid-year check, prepare for summer releases
**Q3 (July)**: Summer review, prepare for fall releases
**Q4 (October)**: Year-end review, plan for next year

---

## Contact & Support

**Review Owner**: Technical Lead
**Questions**: #documentation Slack channel
**Review Results**: Share in team meeting

---

## Related Documentation

- [DOCUMENTATION_MAINTENANCE.md](DOCUMENTATION_MAINTENANCE.md) - Ongoing maintenance guidelines
- [README.md](README.md) - Documentation index

---

**Last Updated**: 2025-01-07
**Version**: 1.0
**Next Review**: April 2025 (Q2)
