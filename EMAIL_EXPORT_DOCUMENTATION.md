# Email Export Feature Documentation

## Overview

The email export feature allows users to export counties and towns data as CSV or PDF files, which are then sent to a specified email address as attachments.

## Features

- **Export Counties as CSV** - Get a CSV file of all counties (or filtered) sent via email
- **Export Counties as PDF** - Get a PDF file of all counties (or filtered) sent via email
- **Export Towns as CSV** - Get a CSV file of all towns (or filtered) sent via email
- **Export Towns as PDF** - Get a PDF file of all towns (or filtered) sent via email

All exports respect the current search/filter query applied in the UI.

## Backend Implementation

### API Endpoints

```php
POST /api/export/counties/csv
POST /api/export/counties/pdf
POST /api/export/towns/csv
POST /api/export/towns/pdf
```

### Request Format

```json
{
  "email": "user@example.com",
  "query": "optional search term"
}
```

### Response Format

**Success (200):**
```json
{
  "message": "Export sent successfully to user@example.com"
}
```

**Validation Error (422):**
```json
{
  "error": {
    "email": ["The email field is required."]
  }
}
```

**No Data (400):**
```json
{
  "error": "No data to export"
}
```

**Server Error (500):**
```json
{
  "error": "Failed to send email: [error message]"
}
```

## Frontend Usage

### UI Buttons

Four email export buttons are added to the main page:

1. **📧 Email CSV** (Counties) - Blue button
2. **📧 Email PDF** (Counties) - Indigo button
3. **📧 Email CSV** (Towns) - Blue button
4. **📧 Email PDF** (Towns) - Indigo button

### User Flow

1. User optionally enters a search query to filter data
2. User clicks one of the email export buttons
3. A modal appears asking for an email address
4. User enters their email and clicks "Send Email"
5. The export is generated and sent to the specified email
6. User receives a confirmation message

## Mail Configuration

By default, the application uses the `log` mail driver (configured in `.env`):

```env
MAIL_MAILER=log
```

This means emails are written to `storage/logs/laravel.log` instead of being sent via SMTP.

### To Enable Actual Email Sending

Update your `.env` file with SMTP settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Technical Details

### Dependencies

- **barryvdh/laravel-dompdf** - PDF generation library

### Controllers

- `App\Http\Controllers\ExportController` - Handles all export requests

### Mail Classes

- `App\Mail\ExportMail` - Mailable class for sending exports

### Views

- `resources/views/emails/export.blade.php` - Email template
- `resources/views/exports/counties-pdf.blade.php` - Counties PDF template
- `resources/views/exports/towns-pdf.blade.php` - Towns PDF template

### Important Notes

1. **PDF Limits**: Towns PDF exports are limited to 1000 records to prevent memory issues
2. **CSV No Limits**: CSV exports have no record limit
3. **File Cleanup**: Export files are automatically deleted after sending
4. **Temporary Storage**: Files are stored in `storage/app/exports/` before sending

## Testing

Run the export tests:

```bash
php artisan test --filter=ExportTest
```

All 6 export tests should pass:
- ✓ export counties csv requires email
- ✓ export counties csv sends email
- ✓ export counties pdf sends email
- ✓ export towns csv sends email
- ✓ export towns csv with query filter
- ✓ export returns error with no data

## Example API Usage

Using cURL:

```bash
curl -X POST http://127.0.0.1:8000/api/export/counties/csv \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","query":"Pest"}'
```

Using PowerShell:

```powershell
Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/export/counties/csv" `
  -Method POST `
  -Body (@{email="user@example.com";query="Pest"} | ConvertTo-Json) `
  -ContentType "application/json"
```

## Troubleshooting

### Email Not Received

1. Check `MAIL_MAILER` is set to `smtp` (not `log`)
2. Verify SMTP credentials in `.env`
3. Check `storage/logs/laravel.log` for errors

### Memory Issues with Large Exports

- PDF exports are limited to 1000 records
- Use CSV for larger datasets
- Consider adding pagination in the UI

### Export Files Not Deleted

- Ensure the application has write permissions to `storage/app/exports/`
- Check Laravel logs for file operation errors
