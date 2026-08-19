$pgCtl = "C:\laragon\pg_ctl"
if (Test-Path $pgCtl) {
    Write-Host "pg_ctl found: $pgCtl"
    Write-Host "File size: $(Get-Item $pgCtl).Length bytes"
    # Try to get info about it
    $content = Get-Content $pgCtl -TotalCount 5
    Write-Host "First 5 lines:"
    $content | ForEach-Object { Write-Host $_ }
} else {
    Write-Host "pg_ctl not found at $pgCtl"
}