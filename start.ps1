$ErrorActionPreference = 'Stop'
$phpCommand = Get-Command php -ErrorAction SilentlyContinue
$phpExecutable = if ($phpCommand) { $phpCommand.Source } else { $null }
if (!$phpExecutable) {
    foreach ($candidate in @('C:\xampp\new install\php\php.exe', 'C:\xampp\php\php.exe', 'C:\php\php.exe')) {
        if (Test-Path -LiteralPath $candidate) {
            $phpExecutable = $candidate
            break
        }
    }
}
if (!$phpExecutable) {
    throw 'PHP was not found. Install PHP 8.1+ with PDO MySQL and add php.exe to PATH.'
}
$publicDirectory = Join-Path $PSScriptRoot 'public'
Write-Host 'Open http://127.0.0.1:8000/stockroom.php. Press Ctrl+C to stop the server.'
Write-Host 'Keep this terminal open while using the app, and keep MySQL running in XAMPP.'
& $phpExecutable -S '127.0.0.1:8000' -t $publicDirectory
exit $LASTEXITCODE
