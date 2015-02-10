# Narnia Guardian
#### N!B! Read everything before begin clean up
### Level - intermediate - there is manual work involved
This tool is created to clean infected PHP files witch contains obfuscated code. There is code sample on StackOverflow for UNIX system's with root access, but not always you would have it + with those samples, you never know what modifications of bad code you have. With method below you can fine tune bad sample library to match your case.

Maybe you are here because of [MailPoet](http://blog.sucuri.net/2014/10/wordpress-websites-continue-to-get-hacked-via-mailpoet-plugin-vulnerability.html)  or [StackOverflow](http://stackoverflow.com/questions/25996752/removing-a-string-in-a-php-file-with-start-and-end/28430880)

If you got this bad code on your server, it could be triggered any time and could do anything on your server. In fact, purpose and content of this malware code also could be changed anytime. Code could be stealing passwords, sending spam e-mail from your IP or even hosting illegal copy of Torrent files and steal traffic you pay for. Once your IP is globally blacklisted, it is hard to get back SEO on Google etc.

## Lyrics
To create this, I have donated two workday's to clean up private server, please contribute with code comments, better descriptions in more fluent language and other suggestions.
So far, my motivation to update is anger on this malware, as I do not code for living and this malware code ruined our multiple site server for non-profit organisations, where I belong. I believe in open source software (OSS) and believe that OSS can be more safer than paid one, if public gives effort to it. There is so many great programmers amongst us, unfortunately, at least as much, there are ones, who use their skills for personal good doing bad things.

---

## How does malware code looks like?
It will be in begining of PHP file and begins with PHP tags as `<?php` and `?>`. This is safest way to ingest this code inside already existing code file. For example **you have**

``` PHP
<?php
/* Here comes my super-duper code */
 
?> // May or may not contain ending tag
```
Or even plain HTML inside .php file

``` HTML
<!DOCTYPE html>
<html>
	<title>Narnia Guardian</title>

etc.
```

**After malware injection file will look something like**
``` PHP
<?php if(!isset($GLOBALS["\x61\156\x75\156\x61"])) { $ua=strtolower($_SERVER["\x48\124\x54\120\x5f\125\x //etc. ending with ?> if you have turned off your editor line break
<?php
/* Here comes my super-duper code */
````

Even hackers have to respect correct syntax of code, he he...

## How does Narnia Guardian works?
It now becomes clear, why I told you all that - 

1. NG will search for PHP files, which contain bad code samples from library (blacklist.txt)
2. Going off that exact location NG will search matching pair of PHP tags right before and right after sample location - the tag pair where bad code lives in
3. IF everything matches up - everything inside those matching pairs of PHP tags will be removed, including tags itself, to maintain clean code 
2. For every case - malware samples are different - **You have to update them in order to clean up your code**

---
## What files does Narnia Guardian contains?

|File					| Role
|-----------------------|---------------------------
|NarniaGuardian.php		| Contains cleaner class
|blacklist.txt			| Here insert library of malware samples
|uniquelist.txt			| List of unique first lines of php files

---
## Order of actions
1. Download / Upload script to test location (strongly suggested)
2. Modify / copy index.php content between first section of `<?php ... ?>`
3. Run script by browsing location on browser
4. Inspect output of script - there will be block's of obfuscated code - right before it, there should be outputted location where it comes from
5. Inspect source of obfucated block file - if it is clear that this is not your code or other good minified code, search for string that could be as key string to recognize it, as example `if(!isset($GLOBALS["\x61\156\x75\156\x61"]))` or meaningless variables `$bmhqhhzolg` or `$pjro=22;$vnlpv=$pjro+42;` - copy these kind of strings to blacklist.txt library - one sample per one line
6. Clean uniquelist.txt content and run again script.
4. Open uniquelist.txt, search for malware code - copy typical sample of code to blaclklist.txt library - one sample per one line
5. Check logs folder for success. The one named root-error[..].log will contain list of files, which are suspicious, but could be some large class file. These should be checked and deleted manually.
1. Repeat steps 3 to 6. If output is much more shorter, it means it is working, don't stop until you are sure that your all of your files are clean.

## TODO:
1. Silent mode to be run behind scenes not distracting with ugly output of numbers and codes.

## What to do next?
* Copy this script to safe place, chmod it for safety. If in bad hands - it could do bad things out of the box.
* Change ALL passwords, I mean ALL - WordPress, databases, WordPress salt, user passwords, secret keys, everything - all paswords could be readed by malware code
* Update your OSS or paid software for latest versions, including WordPress, plugins, extensions, anything you have
* **chmod** correct file permissions for your project. It could be `755` for directory, `644` for files. (**please commit here!**)
* If it is your own code - walk OVER it ALL manually - check if it is escaped from form inputs, SQL injections etc.
* Ask your hosting provider to assign new public IP
* Pray God, that your super-secret files didn't got stolen
* google more about this issue

## Why does `Narnia`?
Project names containing `Narnia` I give if the code is meant to be run into private / hidden locations, without public access. If you see my project containing `Narnia` and you don't have any idea why you see it, it means that you have run into wrong place or something is broken and now you see it. Just like in the movie, it is a real magic...

---
# More N!B! - test it first - You could lose content of all your php files or ruin code insid them.
**I strongly suggest to test clean up script on localhost with corrupted files or at least on copy inside your host. Once I release this to public domain, script runs perfectly, but hackers don't sleep and they could affect their code, so my guardian won't clean it up anymore or affect it so my script will delete more than should.**
