Set-Location C:/laragon/www/SimpegRSHDI
$ErrorActionPreference = 'Stop'

function Get-QuotedItems([string]$text) {
    if ([string]::IsNullOrWhiteSpace($text)) { return @() }
    $matches = [regex]::Matches($text, "'([^']+)'")
    $items = @()
    foreach ($m in $matches) {
        if ($m.Groups[1].Success) { $items += $m.Groups[1].Value }
    }
    return $items
}

$modelFiles = Get-ChildItem app/Models -File -Filter *.php
$controllerFiles = Get-ChildItem app/Http/Controllers -Recurse -File -Filter *.php

$models = @()
foreach ($f in $modelFiles) {
    $content = Get-Content $f.FullName -Raw
    $class = [regex]::Match($content, 'class\s+(\w+)\s+extends\s+\w+').Groups[1].Value
    if (-not $class) { continue }

    $id = "M_$class"

    $fillableBlock = [regex]::Match($content, 'protected\s+\$fillable\s*=\s*\[(.*?)\];', 'Singleline').Groups[1].Value
    $fillable = Get-QuotedItems $fillableBlock

    $castsBlock = [regex]::Match($content, 'protected\s+\$casts\s*=\s*\[(.*?)\];', 'Singleline').Groups[1].Value
    $castKeys = @()
    if ($castsBlock) {
        $castKeys = [regex]::Matches($castsBlock, "'([^']+)'\s*=>") | ForEach-Object { $_.Groups[1].Value }
    }

    $methods = [regex]::Matches($content, 'public\s+function\s+(\w+)\s*\(') | ForEach-Object { $_.Groups[1].Value }

    $rel = @()
    $r1 = [regex]::Matches($content, '\$this->(belongsTo|hasOne|hasMany|belongsToMany)\s*\(\s*([A-Za-z_][A-Za-z0-9_]*)::class')
    foreach ($m in $r1) {
        $rel += [PSCustomObject]@{
            type = $m.Groups[1].Value
            target = $m.Groups[2].Value
        }
    }

    $models += [PSCustomObject]@{
        Id = $id
        Class = $class
        Fillable = ($fillable | Select-Object -Unique)
        CastKeys = ($castKeys | Select-Object -Unique)
        Methods = ($methods | Select-Object -Unique)
        Relations = $rel
    }
}

$controllers = @()
foreach ($f in $controllerFiles) {
    $content = Get-Content $f.FullName -Raw
    if ($content -notmatch 'extends\s+Controller') { continue }

    $class = [regex]::Match($content, 'class\s+(\w+)\s+extends').Groups[1].Value
    if (-not $class) { continue }

    $marker = 'app\Http\Controllers\'
    $index = $f.FullName.IndexOf($marker)
    if ($index -ge 0) {
        $suffix = $f.FullName.Substring($index + $marker.Length).Replace('.php', '').Replace('\\', '_')
    }
    else {
        $suffix = $f.BaseName
    }
    $id = "C_$suffix"

    $props = [regex]::Matches($content, '(?:public|protected|private)\s+\$([A-Za-z_][A-Za-z0-9_]*)\s*;') | ForEach-Object { $_.Groups[1].Value }
    $methods = [regex]::Matches($content, 'public\s+function\s+(\w+)\s*\(') | ForEach-Object { $_.Groups[1].Value }
    $imports = [regex]::Matches($content, 'use\s+App\\Models\\([A-Za-z_][A-Za-z0-9_]*)\s*;') | ForEach-Object { $_.Groups[1].Value }

    $controllers += [PSCustomObject]@{
        Id = $id
        Class = $class
        NamespaceHint = $suffix
        Props = ($props | Select-Object -Unique)
        Methods = ($methods | Select-Object -Unique)
        ModelImports = ($imports | Select-Object -Unique)
    }
}

$lines = New-Object System.Collections.Generic.List[string]
$lines.Add('classDiagram')
$lines.Add('direction LR')
$lines.Add('')
$lines.Add('%% MODEL CLASSES')

foreach ($m in $models | Sort-Object Class) {
    $lines.Add("class $($m.Id) as $($m.Class) {")

    if ($m.Fillable.Count -gt 0) {
        foreach ($a in $m.Fillable) { $lines.Add("  +$a") }
    } else {
        $lines.Add('  +<no_fillable_defined>')
    }

    if ($m.CastKeys.Count -gt 0) {
        foreach ($c in $m.CastKeys) { $lines.Add("  #cast_$c") }
    }

    if ($m.Methods.Count -gt 0) {
        foreach ($fn in $m.Methods) { $lines.Add("  +$fn()") }
    } else {
        $lines.Add('  +<no_public_method>()')
    }

    $lines.Add('}')
}

$lines.Add('')
$lines.Add('%% CONTROLLER CLASSES')

foreach ($c in $controllers | Sort-Object NamespaceHint) {
    $label = "$($c.Class)\\n[$($c.NamespaceHint)]"
    $lines.Add(('class {0}["{1}"]' -f $c.Id, $label))
    $lines.Add("class $($c.Id) {")

    if ($c.Props.Count -gt 0) {
        foreach ($p in $c.Props) { $lines.Add("  -$p") }
    } else {
        $lines.Add('  -<stateless_controller>')
    }

    if ($c.Methods.Count -gt 0) {
        foreach ($fn in $c.Methods) { $lines.Add("  +$fn()") }
    } else {
        $lines.Add('  +<no_public_method>()')
    }

    $lines.Add('}')
}

$lines.Add('')
$lines.Add('%% MODEL RELATIONSHIPS')
$relSet = New-Object System.Collections.Generic.HashSet[string]

foreach ($m in $models) {
    foreach ($r in $m.Relations) {
        $target = $models | Where-Object { $_.Class -eq $r.target } | Select-Object -First 1
        if (-not $target) { continue }

        $line = switch ($r.type) {
            'belongsTo' { "$($m.Id) --> $($target.Id) : belongsTo"; break }
            'hasOne' { "$($m.Id) --> $($target.Id) : hasOne"; break }
            'hasMany' { ('{0} --> "*" {1} : hasMany' -f $m.Id, $target.Id); break }
            'belongsToMany' { ('{0} "*" --> "*" {1} : belongsToMany' -f $m.Id, $target.Id); break }
            default { "$($m.Id) --> $($target.Id) : $($r.type)" }
        }

        if ($relSet.Add($line)) { $lines.Add($line) }
    }
}

$lines.Add('')
$lines.Add('%% CONTROLLER -> MODEL DEPENDENCIES')
$depSet = New-Object System.Collections.Generic.HashSet[string]

foreach ($c in $controllers) {
    foreach ($mi in $c.ModelImports) {
        $target = $models | Where-Object { $_.Class -eq $mi } | Select-Object -First 1
        if (-not $target) { continue }
        $dep = "$($c.Id) ..> $($target.Id) : uses"
        if ($depSet.Add($dep)) { $lines.Add($dep) }
    }
}

$targetFile = 'docs/class-diagram-controller-model.mermaid'
$lines | Set-Content $targetFile -Encoding UTF8
Write-Output "Generated $targetFile with $($lines.Count) lines"
