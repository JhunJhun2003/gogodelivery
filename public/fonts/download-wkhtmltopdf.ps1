[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$wc = New-Object System.Net.WebClient
$wc.Headers.Add('User-Agent', 'Mozilla/5.0')
$wc.DownloadFile('https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6.1-3/wkhtmltox-0.12.6.1-3.msvc2015-win64.exe', 'C:\Users\Admin\Desktop\no end\wkhtmltopdf-installer.exe')
Write-Output "Downloaded"
(Get-Item 'C:\Users\Admin\Desktop\no end\wkhtmltopdf-installer.exe').Length
