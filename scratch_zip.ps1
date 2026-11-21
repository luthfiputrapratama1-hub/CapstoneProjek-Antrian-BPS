Add-Type -AssemblyName System.IO.Compression.FileSystem

$source = 'C:\Users\Lenovo\.gemini\antigravity\scratch\CapstoneProject-master'
$destination = 'C:\Users\Lenovo\.gemini\antigravity\brain\b3f6d1c4-6e4b-4fb3-9401-debb2b101068\CapstoneProject-PST-BPS-Final.zip'

if (Test-Path $destination) {
    Remove-Item $destination -Force
}

Write-Output "Mulai mengompres direktori $source ke $destination ..."
[System.IO.Compression.ZipFile]::CreateFromDirectory($source, $destination, [System.IO.Compression.CompressionLevel]::Optimal, $false)

if (Test-Path $destination) {
    $item = Get-Item $destination
    $mb = [math]::Round($item.Length / 1MB, 2)
    Write-Output "SUKSES: Berkas ZIP berhasil dibuat ($mb MB) di $($item.FullName)"
} else {
    Write-Error "GAGAL: Berkas ZIP tidak ditemukan."
}
