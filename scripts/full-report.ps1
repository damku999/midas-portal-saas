# Comprehensive Code Quality Report
# Run with: powershell -ExecutionPolicy Bypass -File scripts\full-report.ps1

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Comprehensive Code Quality Report" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$reportFile = "claudedocs\code-quality-report-$(Get-Date -Format 'yyyy-MM-dd-HHmm').md"

# Initialize report
$report = @"
# Code Quality Report
Generated: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')

## Summary

"@

# 1. Code Style Issues
Write-Host "[1/7] Analyzing code style with Laravel Pint..." -ForegroundColor Yellow
$pintOutput = & vendor\bin\pint --test 2>&1 | Out-String
$report += @"

## 1. Code Style Analysis (Laravel Pint)

``````
$pintOutput
``````

"@

# 2. Static Analysis
Write-Host "[2/7] Running static analysis with PHPStan..." -ForegroundColor Yellow
if (Test-Path vendor\bin\phpstan) {
    $phpstanOutput = & vendor\bin\phpstan analyse --error-format=table --memory-limit=2G 2>&1 | Out-String
    $report += @"

## 2. Static Analysis (PHPStan)

``````
$phpstanOutput
``````

"@
} else {
    Write-Host "PHPStan not installed, skipping..." -ForegroundColor Gray
    $report += "`n## 2. Static Analysis`n`nPHPStan not installed.`n"
}

# 3. Security Audit
Write-Host "[3/7] Running security audit..." -ForegroundColor Yellow
$securityOutput = composer audit --format=plain 2>&1 | Out-String
$report += @"

## 3. Security Audit

``````
$securityOutput
``````

"@

# 4. Dependency Analysis
Write-Host "[4/7] Analyzing dependencies..." -ForegroundColor Yellow
$depsOutput = composer outdated --direct 2>&1 | Out-String
$report += @"

## 4. Outdated Dependencies

``````
$depsOutput
``````

"@

# 5. File Statistics
Write-Host "[5/7] Gathering file statistics..." -ForegroundColor Yellow
$phpFiles = (Get-ChildItem -Path app -Recurse -Filter *.php).Count
$testFiles = (Get-ChildItem -Path tests -Recurse -Filter *.php -ErrorAction SilentlyContinue).Count
$viewFiles = (Get-ChildItem -Path resources\views -Recurse -Filter *.blade.php -ErrorAction SilentlyContinue).Count
$migrationFiles = (Get-ChildItem -Path database\migrations -Filter *.php -ErrorAction SilentlyContinue).Count

$report += @"

## 5. Project Statistics

- PHP Files (app/): $phpFiles
- Test Files: $testFiles
- Blade Templates: $viewFiles
- Migrations: $migrationFiles

"@

# 6. Code Metrics
Write-Host "[6/7] Calculating code metrics..." -ForegroundColor Yellow
$totalLines = 0
Get-ChildItem -Path app -Recurse -Filter *.php | ForEach-Object {
    $totalLines += (Get-Content $_.FullName).Count
}

$report += @"

## 6. Code Metrics

- Total Lines of Code (app/): ~$totalLines

"@

# 7. Common Issues Check
Write-Host "[7/7] Checking for common issues..." -ForegroundColor Yellow

$issues = @()

# Check for TODO comments
$todoCount = (Select-String -Path "app\**\*.php" -Pattern "TODO|FIXME|XXX" -ErrorAction SilentlyContinue).Count
if ($todoCount -gt 0) {
    $issues += "- Found $todoCount TODO/FIXME comments in code"
}

# Check for debug statements
$debugCount = (Select-String -Path "app\**\*.php" -Pattern "dd\(|dump\(|var_dump\(" -ErrorAction SilentlyContinue).Count
if ($debugCount -gt 0) {
    $issues += "- Found $debugCount potential debug statements (dd, dump, var_dump)"
}

# Check for disabled tests
$skipCount = (Select-String -Path "tests\**\*.php" -Pattern "->skip\(|->markTestSkipped" -ErrorAction SilentlyContinue).Count
if ($skipCount -gt 0) {
    $issues += "- Found $skipCount skipped/disabled tests"
}

$report += @"

## 7. Common Issues

$($issues -join "`n")

"@

# Add recommendations
$report += @"

## Recommendations

### Immediate Actions
1. Run ``vendor\bin\pint`` to auto-fix code style issues
2. Review and fix critical PHPStan errors
3. Update security-vulnerable dependencies
4. Remove debug statements before deployment

### Code Quality Improvements
1. Add PHPDoc type hints to reduce static analysis errors
2. Implement missing unit tests for critical services
3. Review and complete TODO items
4. Consider adding Larastan for Laravel-specific analysis

### Commands to Fix Issues

``````bash
# Fix code style
vendor\bin\pint

# Clear caches
php artisan optimize:clear

# Update dependencies
composer update --with-dependencies

# Run tests
php artisan test
``````

"@

# Save report
$report | Out-File -FilePath $reportFile -Encoding UTF8

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  Report Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Report saved to: $reportFile" -ForegroundColor Cyan
Write-Host ""
Write-Host "Quick fixes available:" -ForegroundColor Yellow
Write-Host "  scripts\quick-fix.bat" -ForegroundColor White
Write-Host ""
