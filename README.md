# tina4php-firebird

### Installation
```
composer require tina4stack/tina4php-firebird
```

### Testing with Docker
```
docker run --platform linux/x86_64 -p 33050:3050 -e ISC_PASSWORD=pass1234 -e FIREBIRD_DATABASE=TINA4.FDB -e FIREBIRD_USER=firebird jacobalberty/firebird:3.0
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