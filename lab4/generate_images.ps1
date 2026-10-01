Add-Type -AssemblyName System.Drawing
for ($i = 1; $i -le 25; $i++) {
    $bmp = New-Object System.Drawing.Bitmap 300, 200
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $r = ($i * 37) % 180 + 50
    $gr = ($i * 73) % 180 + 50
    $b = ($i * 109) % 180 + 50
    $brush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb($r, $gr, $b))
    $g.FillRectangle($brush, 0, 0, 300, 200)
    $font = New-Object System.Drawing.Font('Arial', 20, [System.Drawing.FontStyle]::Bold)
    $textBrush = [System.Drawing.Brushes]::White
    $g.DrawString("Image #$i", $font, $textBrush, 80, 80)
    $targetPath = "C:\Users\Kozak\Desktop\labs\year2\Advanced_Web_Development\lab4\image\img_$i.jpg"
    $bmp.Save($targetPath, [System.Drawing.Imaging.ImageFormat]::Jpeg)
    $g.Dispose()
    $brush.Dispose()
    $font.Dispose()
    $bmp.Dispose()
}
Write-Output "Done creating 25 images"
