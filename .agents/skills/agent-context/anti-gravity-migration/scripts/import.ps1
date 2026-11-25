<#
.SYNOPSIS
    Imports Antigravity IDE data, brain artifacts, conversations, knowledge, configs, and project .agents/AGENTS.md onto a new machine.
.DESCRIPTION
    Restores Antigravity local data exported from another Windows 11 machine.
.PARAMETER Zip
    Path to the antigravity-export-*.zip file created by export.ps1.
.PARAMETER ProjectsRoot
    Root folder where projects should be placed on this new machine (e.g. "C:\Coding Projects\web-projects").
.PARAMETER Force
    Bypasses the check for running Antigravity IDE processes.
.PARAMETER SkipBackup
    Skips creating a safety backup of existing .gemini\antigravity-ide on this machine.
#>
[CmdletBinding()]
param (
    [Parameter(Mandatory = $true)]
    [string]$Zip,

    [string]$ProjectsRoot = "C:\Coding Projects\web-projects",

    [switch]$Force = $false,
    [switch]$SkipBackup = $false
)

$ErrorActionPreference = "Stop"

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host " Antigravity IDE Migration - Import Tool  " -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

# 1. Process Guard
$runningProcesses = Get-Process | Where-Object { $_.ProcessName -match "antigravity" }
if ($runningProcesses -and -not $Force) {
    Write-Warning "Antigravity IDE is currently running ($($runningProcesses.Count) process instances detected)."
    Write-Warning "Importing files while active can cause corruption or permission locks."
    Write-Host "Please save your work, completely quit Antigravity IDE, and rerun this script." -ForegroundColor Yellow
    Write-Host "(Or re-run with -Force if you understand the risks)." -ForegroundColor DarkGray
    exit 1
}

# 2. Check Archive Exists
if (-not (Test-Path -Path $Zip)) {
    Write-Error "ZIP archive not found at: $Zip"
    exit 1
}

$resolvedZip = (Resolve-Path $Zip).Path
Write-Host "[1/5] Unpacking archive from: $resolvedZip..." -ForegroundColor Green

$stagingDir = Join-Path $env:TEMP "antigravity-import-staging-$((Get-Date).ToString('yyyyMMdd-HHmmss'))"
New-Item -ItemType Directory -Path $stagingDir -Force | Out-Null

try {
    Expand-Archive -Path $resolvedZip -DestinationPath $stagingDir -Force

    $manifestPath = Join-Path $stagingDir "manifest.json"
    $manifest = $null
    if (Test-Path $manifestPath) {
        $manifest = Get-Content -Path $manifestPath -Raw | ConvertFrom-Json
        Write-Host "  -> Archive generated on: $($manifest.exportDate)" -ForegroundColor DarkGray
        Write-Host "  -> Source machine: $($manifest.hostName) (user: $($manifest.userName))" -ForegroundColor DarkGray
    }

    $geminiRoot = "$env:USERPROFILE\.gemini"
    if (-not (Test-Path $geminiRoot)) {
        New-Item -ItemType Directory -Path $geminiRoot -Force | Out-Null
    }

    $targetIde = Join-Path $geminiRoot "antigravity-ide"
    $targetConfig = Join-Path $geminiRoot "config"

    # 3. Safety Backup of Existing Data
    if (-not $SkipBackup) {
        $backupTimestamp = (Get-Date).ToString("yyyyMMdd-HHmmss")
        if (Test-Path $targetIde) {
            $ideBackup = "$geminiRoot\antigravity-ide.bak-$backupTimestamp"
            Write-Host "[2/5] Creating safety backup of existing IDE data to $ideBackup..." -ForegroundColor Green
            Copy-Item -Path $targetIde -Destination $ideBackup -Recurse -Force
        }
        if (Test-Path $targetConfig) {
            $configBackup = "$geminiRoot\config.bak-$backupTimestamp"
            Write-Host "  -> Creating safety backup of config to $configBackup..." -ForegroundColor DarkGray
            Copy-Item -Path $targetConfig -Destination $configBackup -Recurse -Force
        }
    } else {
        Write-Host "[2/5] Skipping safety backup (-SkipBackup specified)..." -ForegroundColor Yellow
    }

    # 4. Restore Antigravity IDE Data
    Write-Host "[3/5] Restoring conversations, brain artifacts, and knowledge..." -ForegroundColor Green
    $stagedIdeData = Join-Path $stagingDir "gemini-data\antigravity-ide"
    if (Test-Path $stagedIdeData) {
        if (-not (Test-Path $targetIde)) {
            New-Item -ItemType Directory -Path $targetIde -Force | Out-Null
        }
        Copy-Item -Path "$stagedIdeData\*" -Destination $targetIde -Recurse -Force
        Write-Host "  -> Restored successfully to $targetIde" -ForegroundColor DarkGray
    }

    # Restore Config
    $stagedConfig = Join-Path $stagingDir "gemini-data\config"
    if (Test-Path $stagedConfig) {
        Write-Host "  -> Restoring global configs (.gemini\config)..." -ForegroundColor DarkGray
        if (-not (Test-Path $targetConfig)) {
            New-Item -ItemType Directory -Path $targetConfig -Force | Out-Null
        }
        Copy-Item -Path "$stagedConfig\*" -Destination $targetConfig -Recurse -Force
    }

    # Restore Global Rules if present
    $stagedGlobalRules = Join-Path $stagingDir "gemini-data\GEMINI.md"
    if (Test-Path $stagedGlobalRules) {
        Write-Host "  -> Restoring global rules (.gemini\GEMINI.md)..." -ForegroundColor DarkGray
        Copy-Item -Path $stagedGlobalRules -Destination (Join-Path $geminiRoot "GEMINI.md") -Force
    }

    # 5. Restore Project Rules (.agents & AGENTS.md)
    Write-Host "[4/5] Restoring project-level rules (.agents/AGENTS.md)..." -ForegroundColor Green
    $stagedProjects = Join-Path $stagingDir "projects"

    if (Test-Path $stagedProjects) {
        if (-not (Test-Path $ProjectsRoot)) {
            New-Item -ItemType Directory -Path $ProjectsRoot -Force | Out-Null
        }

        $projectDirs = Get-ChildItem -Path $stagedProjects -Directory
        foreach ($pDir in $projectDirs) {
            $projDest = Join-Path $ProjectsRoot $pDir.Name
            if (-not (Test-Path $projDest)) {
                Write-Host "  -> Creating destination folder for project: $projDest" -ForegroundColor DarkGray
                New-Item -ItemType Directory -Path $projDest -Force | Out-Null
            }

            # Copy .agents
            $agentsSrc = Join-Path $pDir.FullName ".agents"
            if (Test-Path $agentsSrc) {
                Write-Host "  -> Restoring .agents into $projDest\.agents" -ForegroundColor DarkGray
                Copy-Item -Path $agentsSrc -Destination $projDest -Recurse -Force
            }

            # Copy .agent (alternative name)
            $agentSrcAlt = Join-Path $pDir.FullName ".agent"
            if (Test-Path $agentSrcAlt) {
                Write-Host "  -> Restoring .agent into $projDest\.agent" -ForegroundColor DarkGray
                Copy-Item -Path $agentSrcAlt -Destination $projDest -Recurse -Force
            }

            # Copy AGENTS.md
            $agentsMdSrc = Join-Path $pDir.FullName "AGENTS.md"
            if (Test-Path $agentsMdSrc) {
                Write-Host "  -> Restoring AGENTS.md into $projDest\AGENTS.md" -ForegroundColor DarkGray
                Copy-Item -Path $agentsMdSrc -Destination $projDest -Force
            }
        }
    }

    Write-Host "`n==========================================" -ForegroundColor Green
    Write-Host " IMPORT COMPLETED SUCCESSFULLY!           " -ForegroundColor Green
    Write-Host "==========================================" -ForegroundColor Green
    Write-Host "`nVerification Steps:" -ForegroundColor Cyan
    Write-Host "1. Launch Antigravity IDE on this machine."
    Write-Host "2. Open the project folder (e.g. $ProjectsRoot\HRU Basud)."
    Write-Host "3. Verify your past conversations are present in the chat history."
    Write-Host "4. Check that Knowledge items and .agents rules are recognized."
    Write-Host "5. (Optional) Once everything works, you can safely remove the backup folders in $geminiRoot" -ForegroundColor DarkGray
}
finally {
    if (Test-Path $stagingDir) {
        Remove-Item -Path $stagingDir -Recurse -Force -ErrorAction SilentlyContinue
    }
}

