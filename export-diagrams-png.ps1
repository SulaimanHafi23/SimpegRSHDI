# PowerShell Script to export Mermaid diagrams to PNG using Mermaid.ink API
$sourceFolder = "C:\laragon\www\SimpegRSHDI\docs\SequenceDiagram"
$outputFolder = "C:\laragon\www\SimpegRSHDI\docs\SequenceDiagram\PNG"

# Create output folder
if (-not (Test-Path $outputFolder)) {
    New-Item -ItemType Directory -Path $outputFolder -Force | Out-Null
}

Write-Host "Starting PNG export from Mermaid diagrams..." -ForegroundColor Yellow

$mermaidFiles = Get-ChildItem -Path $sourceFolder -Filter "*.mermaid"
$success = 0
$failed = 0

foreach ($file in $mermaidFiles) {
    $fileName = $file.BaseName
    $outputPath = Join-Path $outputFolder "$fileName.png"

    try {
        $content = Get-Content $file.FullName -Raw
        $encoded = [System.Web.HttpUtility]::UrlEncode($content)
        $url = "https://mermaid.ink/img/$encoded"

        Write-Host "Converting $($file.Name)..." -ForegroundColor Cyan -NoNewline

        $client = New-Object System.Net.WebClient
        $client.DownloadFile($url, $outputPath)

        Write-Host " OK" -ForegroundColor Green
        $success++
    }
    catch {
        Write-Host " FAILED" -ForegroundColor Red
        $failed++
    }
}

Write-Host ""
Write-Host "Completed: $success successful, $failed failed" -ForegroundColor Yellow
Write-Host "Output: $outputFolder" -ForegroundColor Cyan
