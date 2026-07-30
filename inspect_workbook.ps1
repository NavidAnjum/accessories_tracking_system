Add-Type -AssemblyName System.IO.Compression.FileSystem

$path = Join-Path $PSScriptRoot 'ed module.xlsx'
$zip = [IO.Compression.ZipFile]::OpenRead($path)

function Get-EntryText([string]$name) {
    $entry = $zip.GetEntry($name)
    if (-not $entry) {
        return $null
    }
    $reader = New-Object IO.StreamReader($entry.Open())
    $text = $reader.ReadToEnd()
    $reader.Close()
    return $text
}

[xml]$sharedXml = Get-EntryText 'xl/sharedStrings.xml'
$shared = @()
foreach ($si in $sharedXml.sst.si) {
    if ($si.t) {
        $shared += [string]$si.t
    } elseif ($si.r) {
        $shared += (($si.r | ForEach-Object { $_.t }) -join '')
    } else {
        $shared += ''
    }
}



[xml]$wbXml = Get-EntryText 'xl/workbook.xml'
[xml]$relsXml = Get-EntryText 'xl/_rels/workbook.xml.rels'
$relMap = @{}
foreach ($rel in $relsXml.Relationships.Relationship) {
    $relMap[$rel.Id] = $rel.Target
}

foreach ($sheet in $wbXml.workbook.sheets.sheet) {
    $name = $sheet.name
    $rid = $sheet.GetAttribute('id', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships')
    $target = 'xl/' + $relMap[$rid]
    [xml]$sheetXml = Get-EntryText $target

    Write-Output "=== $name ==="

    foreach ($row in $sheetXml.worksheet.sheetData.row) {
        $parts = @()
        foreach ($cell in $row.c) {
            $formula = ''
            $value = ''

            if ($cell.f) {
                $formula = [string]$cell.f
            }

            if ($cell.v) {
                $value = [string]$cell.v
                if ($cell.t -eq 's') {
                    $value = $shared[[int]$cell.v]
                }
            }

            if ($formula -or $value) {
                $parts += ($cell.r + ' [f=' + $formula + '] [v=' + $value + ']')
            }
        }

        if ($parts.Count -gt 0) {
            Write-Output ($parts -join ' | ')
        }
    }

    Write-Output ''
}

$zip.Dispose()
