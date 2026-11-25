---
name: antigravity-migration
description: Migrate Google Antigravity data (conversation history, brain artifacts, knowledge/memory, global rules, and per-project .agent folders and AGENTS.md) from one Windows 11 machine to another. Use this skill whenever the user mentions moving, transferring, backing up, exporting, importing, restoring, or syncing Antigravity conversations, memory, knowledge, skills, rules, or .agent/AGENTS.md to a new laptop or device, even if they don't say "migration".
---

# Antigravity Migration (Windows 11)

Moves Antigravity local data between two Windows 11 machines using two automated PowerShell scripts.

## What gets migrated

| Item | Location (Windows 11) | Description |
|---|---|---|
| Conversations, brain artifacts, knowledge/memory, MCP configs | `%USERPROFILE%\.gemini\antigravity-ide\` | Chat histories, transcripts, brain files, local knowledge items |
| Global configurations & tools | `%USERPROFILE%\.gemini\config\` | Global MCP servers, settings, global skill hooks |
| Global rules (if configured) | `%USERPROFILE%\.gemini\GEMINI.md` | User-wide agent rules |
| Project rules, workflows, skills | `<project>\.agents\` (or `.agent\`) | Project-specific customization skills, rules, and plugins |
| Project instructions | `<project>\AGENTS.md` | Repository guidelines and agent instructions |

> [!IMPORTANT]
> Conversations are stored locally on your machine. Signing in with the same Google account on a new laptop does **NOT** restore your chat histories or brain artifacts. You must migrate these files.

## Automated Migration Scripts

The scripts are located in:
`.agents/skills/agent-context/anti-gravity-migration/scripts/`

- [export.ps1](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/skills/agent-context/anti-gravity-migration/scripts/export.ps1): Packages IDE conversations, brain, knowledge, configs, and project `.agents/AGENTS.md` into a single timestamped ZIP file.
- [import.ps1](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/skills/agent-context/anti-gravity-migration/scripts/import.ps1): Restores IDE data and project files on the new laptop, automatically taking a safety backup first.

---

## Step-by-Step Migration Guide

### Phase 1: On Your Current Laptop (Source)

1. **Quit Antigravity IDE completely**:
   - Close the application window.
   - Verify in Task Manager (`Ctrl + Shift + Esc`) that no `Antigravity IDE.exe` processes are still running in the background.

2. **Open PowerShell and run the export script**:
   ```powershell
   cd "D:\User\OneDrive\Desktop\HRU Basud\.agents\skills\agent-context\anti-gravity-migration"
   powershell -ExecutionPolicy Bypass -File .\scripts\export.ps1 -Projects "D:\User\OneDrive\Desktop\HRU Basud"
   ```
   *(If you have multiple projects, pass them separated by commas: `-Projects "D:\path\proj1","D:\path\proj2"`)*

3. **Locate the Export ZIP**:
   - By default, the script generates `antigravity-export-<timestamp>.zip` directly onto your **Desktop**.
   - Copy this ZIP file to your new laptop using a USB drive, local network share, or private cloud storage (OneDrive/Google Drive).

---

### Phase 2: On Your New Laptop (Destination)

1. **Setup Antigravity IDE**:
   - Install **Antigravity IDE** on the new laptop.
   - Open it, sign in with your Google account once, and then **fully quit** the application.

2. **Copy the Migration Scripts & ZIP**:
   - Copy the export ZIP file to a convenient location (e.g. `C:\Users\<User>\Downloads\antigravity-export-*.zip`).
   - Copy or clone your project repository code into your desired directory (e.g., `D:\User\OneDrive\Desktop\HRU Basud`).
   *(Tip: Maintaining the same drive letter and folder path ensures historical conversation file links remain clickable!)*

3. **Run the Import Script in PowerShell**:
   ```powershell
   powershell -ExecutionPolicy Bypass -File ".\scripts\import.ps1" -Zip "C:\path\to\antigravity-export-<timestamp>.zip" -ProjectsRoot "D:\User\OneDrive\Desktop"
   ```
   *What this script does:*
   - Creates a safety backup of any existing `.gemini\antigravity-ide` folder on the new laptop as `antigravity-ide.bak-<timestamp>`.
   - Restores all conversations, brain logs, knowledge items, and MCP configurations.
   - Restores project `.agents` and `AGENTS.md` into `<ProjectsRoot>\<projectName>\`.

---

### Phase 3: Verification

1. Launch **Antigravity IDE** on your new laptop.
2. Open the project folder `D:\User\OneDrive\Desktop\HRU Basud`.
3. Check the left sidebar / conversation list: your past conversations and history will be restored.
4. Verify your project skills, rules, and Knowledge Items load properly.
5. Once verified, you can delete the export ZIP from both machines and remove any `.bak` directories.

---

## Guidance & Best Practices

- **Never export or import while Antigravity IDE is open**: SQLite databases and JSONL logs may be locked or mid-write. The scripts automatically guard against this.
- **Source code is not packaged in the ZIP**: Project source code (git files, node_modules) should be cloned via Git or copied separately; the migration script focuses on conversations, brain artifacts, knowledge, configs, and agent rules.
- **Privacy & Security**: The export archive contains private conversation history and configuration files. Never upload it to a public repository. Delete the ZIP after completing migration.
