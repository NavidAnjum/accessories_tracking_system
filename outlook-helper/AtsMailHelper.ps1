<#
    ATS "Email PI" helper.

    Invoked by the browser via the custom protocol:
        atsmail://open?url=<PI print URL>&subject=<...>&body=<...>&to=<...>&file=<Name>

    What it does, on the user's own PC:
      1. Renders the PI print URL to a PDF using the user's installed Chrome/Edge
         (headless, reusing their real Chrome profile so the app is authenticated).
      2. Opens Outlook (desktop) with a NEW email — To/Subject/Body prefilled and the
         freshly-rendered PI PDF attached.

    No server-side PDF is needed — everything runs locally with the tools each user
    already has (their browser + Outlook).
#>

param(
    [Parameter(Mandatory = $true)] [string]$Uri   # full atsmail://... string
)

$ErrorActionPreference = 'Stop'

function Show-Err([string]$msg) {
    try { Add-Type -AssemblyName System.Windows.Forms | Out-Null;
          [System.Windows.Forms.MessageBox]::Show($msg, 'ATS Email PI', 'OK', 'Error') | Out-Null } catch {}
}

try {
    # ── Parse the atsmail:// URI into a query string ─────────────────────────
    # Example: atsmail://open?url=https%3A%2F%2F...&subject=...&file=...
    $q = ''
    if ($Uri -match '\?(.*)$') { $q = $Matches[1] }
    # protocol handlers sometimes append a trailing slash
    $q = $q.TrimEnd('/')

    $params = @{}
    foreach ($pair in ($q -split '&')) {
        if ($pair -eq '') { continue }
        $kv = $pair -split '=', 2
        $k = [System.Uri]::UnescapeDataString($kv[0])
        $v = if ($kv.Count -gt 1) { [System.Uri]::UnescapeDataString($kv[1]) } else { '' }
        $params[$k] = $v
    }

    $piUrl   = $params['url']
    $subject = if ($params.ContainsKey('subject')) { $params['subject'] } else { 'Proforma Invoice' }
    $body    = if ($params.ContainsKey('body'))    { $params['body'] }    else { '' }
    $to      = if ($params.ContainsKey('to'))      { $params['to'] }      else { '' }
    $fileNm  = if ($params.ContainsKey('file'))    { $params['file'] }    else { 'PI' }

    if (-not $piUrl) { throw "No PI URL was provided." }

    # sanitize a safe file name
    $fileNm = ($fileNm -replace '[\\/:*?"<>|]', '-').Trim()
    if (-not $fileNm) { $fileNm = 'PI' }
    $pdfPath = Join-Path $env:TEMP ("$fileNm.pdf")
    if (Test-Path $pdfPath) { Remove-Item $pdfPath -Force -ErrorAction SilentlyContinue }

    # ── Find a Chromium browser ──────────────────────────────────────────────
    $chrome = $null
    $candidates = @(
        "$env:ProgramFiles\Google\Chrome\Application\chrome.exe",
        "${env:ProgramFiles(x86)}\Google\Chrome\Application\chrome.exe",
        "$env:LocalAppData\Google\Chrome\Application\chrome.exe",
        "$env:ProgramFiles\Microsoft\Edge\Application\msedge.exe",
        "${env:ProgramFiles(x86)}\Microsoft\Edge\Application\msedge.exe"
    )
    foreach ($c in $candidates) { if (Test-Path $c) { $chrome = $c; break } }
    if (-not $chrome) { throw "Chrome or Edge was not found. Install Google Chrome to email the PI." }

    # Use an isolated temporary Chrome profile. The PI URL carries a short-lived,
    # one-time render token which establishes authentication in this profile.
    $srcUserData = Join-Path $env:LocalAppData 'Google\Chrome\User Data'
    $tmpUserData = Join-Path $env:TEMP ('ats-chrome-' + [System.Guid]::NewGuid().ToString('N'))
    New-Item -ItemType Directory -Path $tmpUserData -Force | Out-Null
    try {
        # Copy just the cookie/login state (small) so the headless render is authenticated.
        $srcDefault = Join-Path $srcUserData 'Default'
        $dstDefault = Join-Path $tmpUserData 'Default'
        New-Item -ItemType Directory -Path $dstDefault -Force | Out-Null
        foreach ($f in @('Network\Cookies','Cookies','Local State','Login Data')) {
            $sp = Join-Path $srcUserData $f
            if (-not (Test-Path $sp)) { $sp = Join-Path $srcDefault (Split-Path $f -Leaf) }
            if (Test-Path $sp) {
                $dp = Join-Path $dstDefault (Split-Path $f -Leaf)
                Copy-Item $sp $dp -Force -ErrorAction SilentlyContinue
            }
        }
    } catch {}

    # ── Render the PI URL → PDF (headless) ───────────────────────────────────
    function Render-PiPdf([int]$virtualTimeMs) {
        $renderArgs = @(
            '--headless=new',
            '--disable-gpu',
            '--no-first-run',
            '--no-default-browser-check',
            "--user-data-dir=$tmpUserData",
            '--run-all-compositor-stages-before-draw',
            "--virtual-time-budget=$virtualTimeMs",
            "--print-to-pdf=$pdfPath",
            '--print-to-pdf-no-header',
            $piUrl
        )
        Start-Process -FilePath $chrome -ArgumentList $renderArgs -PassThru -WindowStyle Hidden -Wait | Out-Null
    }

    # PI pages fetch their order data after the HTML shell loads. Give the first
    # render enough time for authentication, API data, fonts, images and layout.
    Render-PiPdf 20000

    # give the file a moment to flush
    $waited = 0
    while (-not (Test-Path $pdfPath) -and $waited -lt 15) { Start-Sleep -Milliseconds 500; $waited++ }
    if (-not (Test-Path $pdfPath)) { throw "Could not generate the PI PDF from the print page." }

    # A blank Chrome PDF is normally only a few KB. Retry once with the same
    # authenticated temp profile and a longer render budget before giving up.
    if ((Get-Item $pdfPath).Length -lt 12000) {
        Remove-Item $pdfPath -Force -ErrorAction SilentlyContinue
        Render-PiPdf 45000
        $waited = 0
        while (-not (Test-Path $pdfPath) -and $waited -lt 30) { Start-Sleep -Milliseconds 500; $waited++ }
    }
    if (-not (Test-Path $pdfPath) -or (Get-Item $pdfPath).Length -lt 12000) {
        throw "Chrome generated a blank PI PDF. Please try again after the PI finishes loading."
    }

    # ── Open Outlook with the PDF attached ───────────────────────────────────
    $outlook = New-Object -ComObject Outlook.Application
    $mail = $outlook.CreateItem(0)  # 0 = olMailItem
    if ($to)      { $mail.To = $to }
    $mail.Subject = $subject
    $mail.Body    = $body
    $mail.Attachments.Add($pdfPath) | Out-Null
    $mail.Display($false)  # show the compose window, don't send

    # cleanup the temp chrome profile in the background
    Start-Job { param($d) Start-Sleep 5; Remove-Item $d -Recurse -Force -ErrorAction SilentlyContinue } -ArgumentList $tmpUserData | Out-Null
}
catch {
    Show-Err ("Could not open the PI email.`r`n`r`n" + $_.Exception.Message)
    exit 1
}
