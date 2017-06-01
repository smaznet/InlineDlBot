# InlineDlBot
Downlaod files inline on telelgram

A simple way to download files using url on telegram with peogress
# Requirements 
php 7.0+
php-curl
php-mbstring

# How To start :

+
```bash
git clone https://github.com/smaznet/InlineDlBot.git
cd InlineDlBot
git submodule update --init --recursive 
 ```

+ edit bot.php and set token and set file url then set dest channel to upload files to it
+ goto to botfather and enable `/setinlinefeedback` and `/setinline`
**note :** bot should be admin on dest channel to sending files

**note :** change line 174 on bot.php and set your user id or remove that to work for all users

[![Telegram](http://trellobot.doomdns.org/telegrambadge.svg)](https://telegram.me/smostafaaz)

