[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$wc = New-Object System.Net.WebClient
$wc.DownloadFile('https://raw.githubusercontent.com/notofonts/myanmar/main/fonts/NotoSansMyanmar/hinted/ttf/NotoSansMyanmar-Regular.ttf', 'C:\Users\Admin\Desktop\no end\gogodelivery\public\fonts\NotoSansMyanmar-Regular.ttf')
(Get-Item 'C:\Users\Admin\Desktop\no end\gogodelivery\public\fonts\NotoSansMyanmar-Regular.ttf').Length
