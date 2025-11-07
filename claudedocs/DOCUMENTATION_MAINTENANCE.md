# Documentation Maintenance Guide

**Version**: 1.0
**Last Updated**: 2025-01-07
**Purpose**: Guidelines for keeping documentation synchronized with codebase changes

---

## Overview

This guide provides systematic procedures for maintaining the Midas Portal documentation as the codebase evolves. Following these guidelines ensures documentation remains accurate, comprehensive, and valuable.

---

## When to Update Documentation

### 1. New Features

**Trigger**: Any new feature, module, or capability added to the system

**Required Updates**:
- Create new feature documentation in `features/` (if major feature)
- Update [FEATURES.md](FEATURES.md) overview
- Add API endpoints to [API_REFERENCE.md](API_REFERENCE.md)
- Update [README.md](README.md) if adding new documentation file
- Add database tables to [core/DATABASE_SCHEMA.md](core/DATABASE_SCHEMA.md)
- Document new services in [core/SERVICE_LAYER.md](core/SERVICE_LAYER.md)

**Example**: Adding "SMS Notifications" feature
```
1. Create features/SMS_NOTIFICATIONS.md
2. Update FEATURES.md → Add SMS under "Multi-Channel Notifications"
3. Update API_REFERENCE.md → Add SMS endpoints section
4. Update README.md → Add to features index
5. Update NOTIFICATION_SYSTEM.md → Add SMS channel
6. Update DATABASE_SCHEMA.md → Add sms_logs table
```

### 2. Modified Features

**Trigger**: Changes to existing feature behavior or implementation

**Required Updates**:
- Update relevant feature documentation in `features/` or `modules/`
- Update code examples if API changes
- Update screenshots if UI changes
- Update workflow diagrams if process changes
- Increment "Last Updated" date

**Example**: Changing trial period from 14 to 30 days
```
1. Update features/SUBSCRIPTION_MANAGEMENT.md → Trial duration
2. Update features/TRIAL_CONVERSION_SYSTEM.md → Trial length
3. Update FEATURES.md → Trial description
4. Update operations/DEPLOYMENT.md → DEFAULT_TRIAL_DAYS config
```

### 3. Deprecated Features

**Trigger**: Feature removal or deprecation

**Required Updates**:
- Mark feature as deprecated in documentation (don't delete immediately)
- Add deprecation notice with timeline
- Update [FEATURES.md](FEATURES.md) to note deprecation
- After deprecation period: Remove documentation or move to archive

**Example**: Deprecating Stripe payment gateway
```
1. Add deprecation notice to features/PAYMENT_GATEWAY_INTEGRATION.md
2. Update FEATURES.md → Mark Stripe as deprecated
3. Update DEPLOYMENT.md → Remove Stripe setup instructions (after removal)
```

### 4. API Changes

**Trigger**: New endpoints, changed parameters, or removed routes

**Required Updates**:
- Update [API_REFERENCE.md](API_REFERENCE.md)
- Update affected feature/module documentation
- Update code examples in all relevant files
- Note breaking changes prominently

**Example**: Adding pagination to `/customers` endpoint
```
1. Update API_REFERENCE.md → Add pagination parameters
2. Update modules/CUSTOMER_MANAGEMENT.md → Show pagination usage
3. Add migration guide if breaking change
```

### 5. Configuration Changes

**Trigger**: New environment variables or configuration options

**Required Updates**:
- Update [setup/ENVIRONMENT_CONFIGURATION.md](setup/ENVIRONMENT_CONFIGURATION.md)
- Update [operations/DEPLOYMENT.md](operations/DEPLOYMENT.md) if deployment-related
- Update relevant feature documentation

**Example**: Adding WHATSAPP_API_VERSION config
```
1. Update setup/ENVIRONMENT_CONFIGURATION.md → Add env var
2. Update features/NOTIFICATION_SYSTEM.md → Document version usage
3. Update operations/DEPLOYMENT.md → Include in production config
```

### 6. Bug Fixes

**Trigger**: Significant bug fixes that change expected behavior

**Required Updates**:
- Update [operations/TROUBLESHOOTING.md](operations/TROUBLESHOOTING.md)
- Add to known issues if workaround needed
- Update affected documentation to reflect correct behavior

**Example**: Fixing webhook signature verification
```
1. Update operations/TROUBLESHOOTING.md → Remove from known issues
2. Update features/PAYMENT_GATEWAY_INTEGRATION.md → Correct verification flow
```

---

## Documentation Update Checklist

### Before Making Changes

- [ ] Identify all affected documentation files
- [ ] Review current documentation for accuracy
- [ ] Check for cross-references that need updating
- [ ] Determine if new documentation file is needed

### While Making Changes

- [ ] Update all affected files simultaneously
- [ ] Maintain consistent terminology across files
- [ ] Update code examples to match new implementation
- [ ] Add cross-references to related documentation
- [ ] Update "Last Updated" dates
- [ ] Increment version numbers if major change

### After Making Changes

- [ ] Review all updated documentation for accuracy
- [ ] Test all code examples
- [ ] Verify all links work correctly
- [ ] Check for broken cross-references
- [ ] Update [README.md](README.md) if structure changed
- [ ] Commit documentation changes with descriptive message

---

## File-Specific Guidelines

### Core Architecture Files

**Location**: `core/`
**Update Frequency**: Quarterly or on major architectural changes
**Files**: DATABASE_SCHEMA.md, SERVICE_LAYER.md, MIDDLEWARE_REFERENCE.md, ARTISAN_COMMANDS.md, MULTI_PORTAL_ARCHITECTURE.md

**When to Update**:
- New database tables or migrations
- New services created
- New middleware added
- New Artisan commands
- Portal routing changes

**Example Commit**:
```
docs(core): Add EmailVerificationService to SERVICE_LAYER.md

- Added new EmailVerificationService under Communication Services
- Updated service count (51 → 52 services)
- Added usage examples and method documentation
- Updated service dependency graph
```

### Feature Documentation

**Location**: `features/`
**Update Frequency**: As features evolve
**Files**: 12 feature-specific files

**When to Update**:
- Feature behavior changes
- New capabilities added
- Configuration options change
- Integration points change

**Example Commit**:
```
docs(features): Update NOTIFICATION_SYSTEM.md with SMS channel

- Added SMS notification channel section
- Updated channel comparison table
- Added Twilio integration examples
- Updated delivery tracking to include SMS status
```

### Module Documentation

**Location**: `modules/`
**Update Frequency**: On business logic changes
**Files**: 7 module-specific files

**When to Update**:
- Business workflows change
- New fields added to models
- Validation rules change
- Reports or analytics change

**Example Commit**:
```
docs(modules): Update POLICY_MANAGEMENT.md with endorsement flow

- Added policy endorsement section
- Updated workflow diagram with endorsement step
- Added endorsement API endpoints
- Updated premium calculation to include endorsement fees
```

### API Reference

**Location**: `API_REFERENCE.md`
**Update Frequency**: On every API change
**Critical**: Must stay synchronized with routes

**When to Update**:
- New endpoints added
- Endpoint parameters change
- Response format changes
- Authentication requirements change

**Automation Tip**: Use `php artisan route:list` to verify all routes documented

**Example Commit**:
```
docs(api): Add policy endorsement endpoints to API_REFERENCE.md

- Added POST /policies/{id}/endorsements
- Added GET /policies/{id}/endorsements
- Added DELETE /endorsements/{id}
- Updated policy section table of contents
- Total routes: 455 → 458
```

### Setup & Operations

**Location**: `setup/`, `operations/`
**Update Frequency**: On deployment or configuration changes
**Files**: ENVIRONMENT_CONFIGURATION.md, DEPLOYMENT.md, TROUBLESHOOTING.md

**When to Update**:
- New environment variables
- Deployment steps change
- New troubleshooting scenarios
- Infrastructure changes

**Example Commit**:
```
docs(operations): Add Redis cluster setup to DEPLOYMENT.md

- Added Section 8: Redis Cluster Configuration
- Updated system requirements (Redis 6.0+)
- Added cluster health check commands
- Updated troubleshooting for cluster issues
```

---

## Version Control Best Practices

### Commit Messages

Use conventional commit format:
```
docs(<scope>): <description>

[optional body]

[optional footer]
```

**Scopes**:
- `core` - Core architecture documentation
- `features` - Feature documentation
- `modules` - Module documentation
- `api` - API reference
- `setup` - Setup guides
- `operations` - Operations/deployment docs
- `general` - README, FEATURES, or multiple files

**Examples**:
```
docs(api): Add 3 new customer endpoints to API_REFERENCE.md
docs(features): Update trial period from 14 to 30 days
docs(modules): Fix incorrect NCB calculation formula in POLICY_MANAGEMENT.md
docs(general): Update README.md with new feature documentation links
```

### Documentation Branches

**For Minor Updates** (typos, clarifications):
- Update directly in feature branch with code changes
- Include in same PR as code changes

**For Major Updates** (new features, restructuring):
- Create separate `docs/feature-name` branch if documentation is extensive
- Can be merged independently or with code changes

**For Documentation-Only Changes**:
- Create `docs/description` branch
- Example: `docs/update-deployment-guide`

---

## Quality Standards

### Content Quality

- [ ] **Accuracy**: All code examples tested and working
- [ ] **Completeness**: All aspects of feature documented
- [ ] **Clarity**: Written for target audience (developers, DevOps, users)
- [ ] **Consistency**: Terminology matches across all files
- [ ] **Currency**: "Last Updated" date is current

### Formatting Standards

- [ ] **Markdown**: Valid GitHub-flavored Markdown
- [ ] **Code Blocks**: Include language for syntax highlighting
- [ ] **Headings**: Hierarchical (H1 → H2 → H3 → H4)
- [ ] **Links**: All internal links use relative paths
- [ ] **Tables**: Properly formatted with headers

### Cross-References

- [ ] **Related Docs**: Every file has "Related Documentation" section
- [ ] **Bidirectional**: If A links to B, B should link to A
- [ ] **Accurate**: All links tested and working
- [ ] **Contextual**: Links include description of what's linked

---

## Quarterly Review Process

**Frequency**: Every 3 months
**Owner**: Technical Lead or Documentation Maintainer
**Time Required**: 4-6 hours

### Review Checklist

See [DOCUMENTATION_REVIEW_CHECKLIST.md](DOCUMENTATION_REVIEW_CHECKLIST.md) for detailed quarterly review process.

**Quick Version**:
1. Verify all API endpoints documented (compare `route:list` output)
2. Check all code examples still work
3. Verify all links functional
4. Update "Last Updated" dates on reviewed files
5. Check for outdated screenshots or diagrams
6. Verify version numbers across files
7. Update documentation statistics in README.md

---

## Common Scenarios

### Scenario 1: Adding New Database Table

**Files to Update**:
1. `core/DATABASE_SCHEMA.md` - Add table schema
2. `API_REFERENCE.md` - Add related endpoints
3. Relevant module/feature doc - Document usage
4. `README.md` - Update table count if mentioned

**Template**:
```markdown
### Table Name: `new_table`

**Purpose**: Brief description

**Columns**:
| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint | No | AUTO_INCREMENT | Primary key |
| ... | ... | ... | ... | ... |

**Indexes**:
- PRIMARY KEY (`id`)
- INDEX `idx_column` (`column`)

**Relationships**:
- Foreign Key: `other_table_id` → `other_tables(id)`

**Migration**: `database/migrations/YYYY_MM_DD_HHMMSS_create_new_table.php`
```

### Scenario 2: Deprecating Feature

**Steps**:
1. Add deprecation notice at top of feature documentation
2. Update FEATURES.md with deprecation status
3. Set removal timeline (minimum 3 months notice)
4. Update API_REFERENCE.md with deprecation warnings
5. After removal: Archive documentation to `claudedocs/archive/`

**Deprecation Notice Template**:
```markdown
> **⚠️ DEPRECATION NOTICE**
>
> This feature is deprecated as of [DATE] and will be removed in version [VERSION].
>
> **Migration Path**: [Link to replacement documentation]
>
> **Timeline**:
> - [DATE]: Deprecated (current)
> - [DATE + 3 months]: Removal
```

### Scenario 3: Breaking API Change

**Steps**:
1. Document old behavior in separate "Legacy" section
2. Add migration guide showing before/after
3. Update all code examples to new API
4. Add prominent warning about breaking change
5. Update version number (major version bump)
6. Add to API changelog

**Breaking Change Template**:
```markdown
## Breaking Changes in v3.0

### Endpoint: GET /customers

**Changed**: Response format

**Before (v2.x)**:
```json
{
  "customers": [...]
}
```

**After (v3.0)**:
```json
{
  "data": [...],
  "meta": { "total": 100, "page": 1 }
}
```

**Migration**: Update response parsing to use `data` instead of `customers`
```

---

## Tools & Automation

### Recommended Tools

**Markdown Linting**:
```bash
# Install markdownlint
npm install -g markdownlint-cli

# Check all documentation
markdownlint claudedocs/**/*.md
```

**Link Checking**:
```bash
# Install markdown-link-check
npm install -g markdown-link-check

# Check all links
find claudedocs -name "*.md" -exec markdown-link-check {} \;
```

**Route Documentation Sync**:
```bash
# Generate route list
php artisan route:list --json > routes.json

# Compare with API_REFERENCE.md
# (Manual review or custom script)
```

### Pre-Commit Hook (Optional)

Create `.git/hooks/pre-commit`:
```bash
#!/bin/bash

# Check if documentation files changed
DOCS_CHANGED=$(git diff --cached --name-only | grep "^claudedocs/")

if [ -n "$DOCS_CHANGED" ]; then
    echo "Documentation files changed. Running checks..."

    # Lint markdown
    markdownlint $DOCS_CHANGED

    if [ $? -ne 0 ]; then
        echo "Markdown linting failed. Please fix errors."
        exit 1
    fi

    echo "Documentation checks passed!"
fi
```

---

## Contact & Support

**Documentation Owner**: Technical Lead
**Questions**: Ask in #documentation Slack channel
**Issues**: Report in GitHub Issues with `documentation` label
**Contributions**: See [CONTRIBUTING.md](../CONTRIBUTING.md)

---

## Related Documentation

- [README.md](README.md) - Documentation index
- [DOCUMENTATION_REVIEW_CHECKLIST.md](DOCUMENTATION_REVIEW_CHECKLIST.md) - Quarterly review process
- [FEATURES.md](FEATURES.md) - Features overview

---

**Last Updated**: 2025-01-07
**Version**: 1.0
**Maintained By**: Technical Documentation Team
