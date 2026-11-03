param([int]$Port = 8011, [switch]$SetupOnly, [switch]$CalendarDemo, [switch]$PackPhotoDemo, [switch]$ConseilLinksDemo)

$ErrorActionPreference = 'Stop'
$reviewRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Set-Location -LiteralPath $reviewRoot

# Isolate the preview from the normal database, SMTP and browser session.
$env:APP_ENV = 'local'
$env:APP_DEBUG = 'true'
$env:APP_URL = "http://127.0.0.1:$Port"
$env:APP_CONFIG_CACHE = Join-Path $reviewRoot 'storage/app/review-preview-config.php'
$env:DB_URL = ''
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = Join-Path $reviewRoot 'storage/app/review-request-demo.sqlite'
$env:MAIL_MAILER = 'log'
$env:LOG_CHANNEL = 'single'
$env:SESSION_DRIVER = 'file'
$env:SESSION_COOKIE = 'olithea_review_demo'
$env:SESSION_DOMAIN = 'null'
$env:SESSION_SECURE_COOKIE = 'false'
$env:CACHE_STORE = 'file'
$env:QUEUE_CONNECTION = 'sync'
$env:OFFER_JOURNEYS_AUTOMATION_ENABLED = 'false'
$env:OFFER_JOURNEYS_EMAIL_ENABLED = 'false'

if (-not (Test-Path -LiteralPath $env:DB_DATABASE)) {
    New-Item -ItemType File -Path $env:DB_DATABASE | Out-Null
}

& php artisan migrate --force
if ($LASTEXITCODE -ne 0) { throw 'Demo database migration failed.' }
$demoSeeder = if ($ConseilLinksDemo) { 'ConseilLinksDemoSeeder' } elseif ($PackPhotoDemo) { 'PackPhotoDemoSeeder' } elseif ($CalendarDemo) { 'CalendarColorDemoSeeder' } else { 'ReviewRequestDemoSeeder' }
& php artisan db:seed --class=$demoSeeder --force
if ($LASTEXITCODE -ne 0) { throw 'Demo seeding failed.' }

if (-not $SetupOnly) {
    Write-Host "Review preview: http://127.0.0.1:$Port/login"
    & php artisan serve --host=127.0.0.1 --port=$Port --no-reload
}
