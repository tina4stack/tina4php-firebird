# tina4php-firebird

Tina4 Firebird database driver.

### Installation

```
composer require tina4stack/tina4php-firebird
```

### Usage

```php
global $DBA;
$DBA = new \Tina4\DataFirebird("localhost/3050:mydb.fdb", "sysdba", "password");
```

### Testing with Docker
```
docker run -d --platform linux/x86_64 -p 33050:3050 -e ISC_PASSWORD=pass1234 -e FIREBIRD_DATABASE=TINA4.FDB -e FIREBIRD_USER=firebird jacobalberty/firebird:3.0
```

```
composer test
```

### MacOS
Install from Firebird 3.0 Package
Start the server
```bash
sudo -u Firebird /Library/Frameworks/Firebird.framework/Resources/bin/fbguard -daemon -forever &
```

```bash
cd /Library/Frameworks/Firebird.framework/Resources/lib 
sudo install_name_tool libfbclient.dylib -id @executable_path/libfbclient.dylib
sudo install_name_tool libib_util.dylib -id @executable_path/libib_util.dylib
sudo install_name_tool libtommath.dylib -id @executable_path/libtommath.dylib
```

Compiling the extension for PHP on an MAC, make sure on ARM you have 64bit PHP installed
```bash
git clone https://github.com/FirebirdSQL/php-firebird.git
cd php-firebird
phpize
CFLAGS='-arch x86_64' CPPFLAGS=-I/Library/Frameworks/Firebird.framework/Headers LDFLAGS=-L/Library/Frameworks/Firebird.framework/Resources/lib ./configure
make
```
---

## Our Sponsors

**Sponsored with 🩵 by Code Infinity**

[<img src="https://codeinfinity.co.za/wp-content/uploads/2025/09/c8e-logo-github.png" alt="Code Infinity" width="100">](https://codeinfinity.co.za/about-open-source-policy?utm_source=github&utm_medium=website&utm_campaign=opensource_campaign&utm_id=opensource)

*Supporting open source communities <span style="color: #1DC7DE;">•</span> Innovate <span style="color: #1DC7DE;">•</span> Code <span style="color: #1DC7DE;">•</span> Empower*
