$ErrorActionPreference = "Stop"

function Get-ProjectRoot {
    return (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
}

function Find-Tool([string]$Name) {
    $cmd = Get-Command $Name -ErrorAction SilentlyContinue
    if ($cmd) { return $cmd.Source }
    $guesses = @(
        "$env:LOCALAPPDATA\Programs\Herd\bin\$Name.exe",
        "$env:USERPROFILE\AppData\Roaming\Composer\vendor\bin\$Name.bat",
        "C:\laragon\bin\php\php-8.3.0-Win32-vs16-x64\$Name.exe",
        "C:\xampp\php\$Name.exe",
        "C:\ProgramData\ComposerSetup\bin\$Name.bat"
    )
    foreach ($path in $guesses) {
        if (Test-Path $path) { return $path }
    }
    return $null
}

function Require-Tool([string]$Name) {
    $path = Find-Tool $Name
    if (-not $path) {
        throw "$Name is missing. Install it and add it to PATH."
    }
    return $path
}
