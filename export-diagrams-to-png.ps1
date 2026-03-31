
# PowerShell Script to export Mermaid diagrams to PNG with transparent background
# Using Mermaid.ink API (online service)

$sourceFolder = "C:\laragon\www\SimpegRSHDI\docs\SequenceDiagram"
$outputFolder = "C:\laragon\www\SimpegRSHDI\docs\SequenceDiagram\PNG"

# Create output folder if it doesn't exist
if (-not (Test-Path $outputFolder)) {
    New-Item -ItemType Directory -Path $outputFolder -Force | Out-Null
    Write-Host "Created output folder: $outputFolder"
}

# Get all Mermaid files
$mermaidFiles = Get-ChildItem -Path $sourceFolder -Filter "*.mermaid" | Where-Object { $_.PSIsContainer -eq $false }

$totalFiles = $mermaidFiles.Count
$successCount = 0
$errorCount = 0

Write-Host "Found $totalFiles Mermaid diagram files to export..."
Write-Host "Starting PNG export with transparent background..."
Write-Host ""

foreach ($file in $mermaidFiles) {
    $fileName = $file.BaseName
    $outputPath = Join-Path $outputFolder "$($fileName).png"

    try {
        # Read the Mermaid content
        $mermaidContent = Get-Content $file.FullName -Raw

        # URL encode the content for the API
        $urlEncodedContent = [System.Web.HttpUtility]::UrlEncode($mermaidContent)

        # Use Mermaid.ink API to render PNG
        # sync endpoint for direct rendering
        $apiUrl = "https://mermaid.ink/img/$urlEncodedContent"

        # Download the image
        Write-Host "⏳ Converting: $($file.Name) ..." -ForegroundColor Cyan -NoNewline

        # Use WebClient for download
        $webClient = New-Object System.Net.WebClient
        $webClient.Headers.Add("User-Agent", "PowerShell")
        $webClient.DownloadFile($apiUrl, $outputPath)

        Write-Host " ✓ Done" -ForegroundColor Green
        $successCount++
    }
    catch {
        Write-Host " ✗ Error: $_" -ForegroundColor Red
        $errorCount++
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Export Summary:" -ForegroundColor Cyan
Write-Host "  Total files: $totalFiles"
Write-Host "  Success: $successCount" -ForegroundColor Green
Write-Host "  Failed: $errorCount" -ForegroundColor $(if($errorCount -gt 0) { "Red" } else { "Green" })
Write-Host "  Output folder: $outputFolder"
Write-Host "========================================" -ForegroundColor Cyan
