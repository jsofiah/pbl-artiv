# Generate file besar untuk test (run di terminal ygy)
fsutil file createnew e2e/img/file-besar-25mb.pdf 26214400

$b = [System.IO.File]::ReadAllBytes("e2e/img/tes-upload-foto.jpg")
$p = New-Object byte[] 5242880
[Array]::Copy($b, $p, $b.Length)
[System.IO.File]::WriteAllBytes("e2e/img/test-data-5mb.jpg", $p)