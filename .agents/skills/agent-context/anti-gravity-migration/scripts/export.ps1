<#
.SYNOPSIS
    Exports Antigravity IDE data, brain artifacts, conversations, knowledge, configs, and project .agents/AGENTS.md for migration.
.DESCRIPTION
    Packages all Antigravity local data on Windows 11 into a zip archive for easy migration to another laptop.
.PARAMETER Projects
    Array of absolute paths to projects containing .agents/.agent and AGENTS.md.
.PARAMETER OutDir
    Destination folder for the export zip archive. Defaults to the user's Desktop.
.PARAMETER Force
    Bypasses the check for running Antigravity IDE processes.
#>
[CmdletBinding()]
param (
    [string[]]$Projects = @("D:\User\OneDrive\Desktop\HRU Basud"),
    [string]$OutDir = "$HOME\Desktop",
    [switch]$Force = $false
)

$ErrorActionPreference = "Stop"

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host " Antigravity IDE Migration - Export Tool  " -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

# 1. Process Guard
$runningProcesses = Get-Process | Where-Object { $_.ProcessName -match "antigravity" }
if ($runningProcesses -and -not $Force) {
    Write-Warning "Antigravity IDE is currently running ($($runningProcesses.Count) process instances detected)."
    Write-Warning "Exporting while active can cause locked files or partial conversation logs."
    Write-Host "Please save your work, completely quit Antigravity IDE, and rerun this script." -ForegroundColor Yellow
    Write-Host "(Or re-run with -Force if you understand the risks)." -ForegroundColor DarkGray
    exit 1
}

# 2. Verify Output Directory
if (-not (Test-Path -Path $OutDir)) {
    New-Item -ItemType Directory -Path $OutDir -Force | Out-Null
}

$stagingDir = Join-Path $env:TEMP "antigravity-export-staging-$((Get-Date).ToString('yyyyMMdd-HHmmss'))"
New-Item -ItemType Directory -Path $stagingDir -Force | Out-Null

try {
    Write-Host "[1/4] Preparing export staging area..." -ForegroundColor Green

    $manifest = @{
        exportDate = (Get-Date).ToString("o")
        hostName = $env:COMPUTERNAME
        userName = $env:USERNAME
        projects = @()
    }

    # 3. Export Antigravity IDE Data
    $ideDataSrc = "$env:USERPROFILE\.gemini\antigravity-ide"
    $ideDataDest = Join-Path $stagingDir "gemini-data\antigravity-ide"

    if (Test-Path $ideDataSrc) {
        Write-Host "[2/4] Collecting Antigravity conversations, brain artifacts, and knowledge..." -ForegroundColor Green
        New-Item -ItemType Directory -Path $ideDataDest -Force | Out-Null

        $itemsToCopy = @("brain", "conversations", "knowledge", "mcp", "html_artifacts", "implicit")
        foreach ($folder in $itemsToCopy) {
            $srcPath = Join-Path $ideDataSrc $folder
            if (Test-Path $srcPath) {
                Write-Host "  -> Copying $folder..." -ForegroundColor DarkGray
                Copy-Item -Path $srcPath -Destination $ideDataDest -Recurse -Force
            }
        }

        # Copy single files (installation_id, etc.)
        Get-ChildItem -Path $ideDataSrc -File | ForEach-Object {
            Copy-Item -Path $_.FullName -Destination $ideDataDest -Force
        }
    } else {
        Write-Warning "Antigravity IDE data folder not found at $ideDataSrc"
    }

    # Export Config
    $configSrc = "$env:USERPROFILE\.gemini\config"
    $configDest = Join-Path $stagingDir "gemini-data\config"
    if (Test-Path $configSrc) {
        Write-Host "  -> Copying global configurations (.gemini\config)..." -ForegroundColor DarkGray
        Copy-Item -Path $configSrc -Destination $configDest -Recurse -Force
    }

    # Export Legacy Data if present
    $legacySrc = "$env:USERPROFILE\.gemini\antigravity"
    if (Test-Path $legacySrc) {
        Write-Host "  -> Copying legacy data (.gemini\antigravity)..." -ForegroundColor DarkGray
        $legacyDest = Join-Path $stagingDir "gemini-data\antigravity"
        Copy-Item -Path $legacySrc -Destination $legacyDest -Recurse -Force
    }

    # Export Global Rules if present
    $globalRulesSrc = "$env:USERPROFILE\.gemini\GEMINI.md"
    if (Test-Path $globalRulesSrc) {
        Write-Host "  -> Copying global rules (.gemini\GEMINI.md)..." -ForegroundColor DarkGray
        Copy-Item -Path $globalRulesSrc -Destination (Join-Path $stagingDir "gemini-data\GEMINI.md") -Force
    }

    # 4. Export Project-Level Instructions & Rules
    Write-Host "[3/4] Packaging project-level rules (.agents/AGENTS.md)..." -ForegroundColor Green
    $projectsDest = Join-Path $stagingDir "projects"
    New-Item -ItemType Directory -Path $projectsDest -Force | Out-Null

    foreach ($proj in $Projects) {
        if (-not (Test-Path $proj)) {
            Write-Warning "Project path does not exist: $proj"
            continue
        }

        $projItem = Get-Item $proj
        $projName = $projItem.Name
        $projStaging = Join-Path $projectsDest $projName
        New-Item -ItemType Directory -Path $projStaging -Force | Out-Null

        $hasPayload = $false

        # Copy .agents or .agent
        $agentsFolder = Join-Path $proj ".agents"
        $agentFolderAlt = Join-Path $proj ".agent"
        if (Test-Path $agentsFolder) {
            Write-Host "  -> [$projName] Copying .agents folder..." -ForegroundColor DarkGray
            Copy-Item -Path $agentsFolder -Destination (Join-Path $projStaging ".agents") -Recurse -Force
            $hasPayload = $true
        } elseif (Test-Path $agentFolderAlt) {
            Write-Host "  -> [$projName] Copying .agent folder..." -ForegroundColor DarkGray
            Copy-Item -Path $agentFolderAlt -Destination (Join-Path $projStaging ".agent") -Recurse -Force
            $hasPayload = $true
        }

        # Copy AGENTS.md
        $agentsMd = Join-Path $proj "AGENTS.md"
        if (Test-Path $agentsMd) {
            Write-Host "  -> [$projName] Copying AGENTS.md..." -ForegroundColor DarkGray
            Copy-Item -Path $agentsMd -Destination $projStaging -Force
            $hasPayload = $true
        }

        if ($hasPayload) {
            $manifest.projects += @{
                name = $projName
                originalPath = $projItem.FullName
            }
        } else {
            Write-Warning "No .agents, .agent, or AGENTS.md found in $proj"
        }
    }

    # Write Manifest
    $manifestJson = $manifest | ConvertTo-Json -Depth 5
    Set-Content -Path (Join-Path $stagingDir "manifest.json") -Value $manifestJson -Encoding UTF8

    # 5. Compress Archive
    $timestamp = (Get-Date).ToString("yyyy-MM-dd-HHmmss")
    $zipPath = Join-Path $OutDir "antigravity-export-$timestamp.zip"

    Write-Host "[4/4] Creating ZIP archive: $zipPath..." -ForegroundColor Green
    if (Test-Path $zipPath) { Remove-Item $zipPath -Force }

    Compress-Archive -Path "$stagingDir\*" -DestinationPath $zipPath -CompressionLevel Optimal

    $zipItem = Get-Item $zipPath
    $sizeMB = [math]::Round(($zipItem.Length / 1MB), 2)

    Write-Host "`n==========================================" -ForegroundColor Green
    Write-Host " EXPORT COMPLETED SUCCESSFULLY!           " -ForegroundColor Green
    Write-Host "==========================================" -ForegroundColor Green
    Write-Host "Archive Location: $zipPath" -ForegroundColor Yellow
    Write-Host "Archive Size    : $sizeMB MB" -ForegroundColor Yellow
    Write-Host "Projects Bundled: $(($manifest.projects.name) -join ', ')" -ForegroundColor Yellow
    Write-Host "`nNext Steps:" -ForegroundColor Cyan
    Write-Host "1. Copy this ZIP file to your other laptop (via USB, OneDrive, or local network)."
    Write-Host "2. On the other laptop, install Antigravity IDE and sign in once."
    Write-Host "3. Run import.ps1 pointing to this zip file."
}
finally {
    if (Test-Path $stagingDir) {
        Remove-Item -Path $stagingDir -Recurse -Force -ErrorAction SilentlyContinue
    }
}

