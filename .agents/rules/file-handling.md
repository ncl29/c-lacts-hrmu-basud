---
trigger: always_on
---

FILE HANDLING RULE (documents and images)

Storage
- Never store document or image contents in the SQL database. Store paths only.
- All files live under one base directory, set in config as FILE_BASE_DIR.
  Default: %LOCALAPPDATA%/<AppName>/files
  (use LOCALAPPDATA, not APPDATA; set it explicitly when running as a
  service, since the default resolves to the service account's profile).
- Organize files as: <type>/<YYYY>/<MM>/<generated-id>.<ext>
  (type = documents | images)

Size limits (configurable in config; defaults below)
- MAX_IMAGE_SIZE_MB = 10
- MAX_DOCUMENT_SIZE_MB = 25
- MAX_TOTAL_STORAGE_GB = 50 (across all of FILE_BASE_DIR)
- Check the size BEFORE writing (from the upload size, or by counting
  bytes while streaming). Stop and delete any partial file if the limit
  is exceeded.
- Reject empty (0-byte) files.
- If a file exceeds its limit, reject it and report the file name,
  its size, and the limit. Do not truncate, compress, or resize it
  unless explicitly instructed.
- Before saving, confirm that adding the file will not exceed
  MAX_TOTAL_STORAGE_GB. If it would, reject it and report the
  current usage.
- Store the actual byte count in size_bytes, measured from the saved
  file rather than trusted from the client.

Database record
- Store only the RELATIVE path, with forward slashes (never absolute paths).
- Also store: original_filename, mime_type, size_bytes, sha256, created_at.

Writing
- Generate the stored filename yourself (UUID or hash). Never use
  user-supplied names in paths.
- Before writing, confirm the resolved path is inside FILE_BASE_DIR.
- Enforce the size limits above before and during the write.
- Write the file first, verify it exists, then insert the DB record.
- If the DB insert fails, delete the file you just wrote.
- Use the sha256 to detect duplicates before saving.

Reading
- Join FILE_BASE_DIR with the stored relative path. Never reconstruct
  paths any other way.
- If the file is missing, report the error and the record ID; do not
  guess or search elsewhere.

Access
- Only the server process reads or writes files. Other devices (via
  Tailscale) request files through the server, never by direct path.